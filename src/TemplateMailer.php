<?php
namespace lroman242\TemplateMailer;

use lroman242\TemplateMailer\Models\Template;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Illuminate\Mail\Message;
use Swift_Message;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Flynsarmy\DbBladeCompiler\Facades\DbView;

class TemplateMailer extends \Illuminate\Mail\Mailer {

    /**
     * Render the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @return string
     */
    protected function getView($view, $data)
    {
        if ($view instanceof View) {
            $data = array_merge($data, ['dateNow' => date('d-m-Y'), 'timeNow' => 'H:i:s', 'dateWordNow' => date('jS F Y') ]);
            return $view->with($data)->render();
        }
        return $this->views->make($view, $data, ['dateNow' => date('d-m-Y'), 'timeNow' => 'H:i:s', 'dateWordNow' => date('jS F Y') ])->render();
    }

    /**
     * Parse the given view name or array.
     *
     * @param  string|array|\Illuminate\View\View  $view
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseView($view)
    {
        if ($view instanceof Template) {
            $view = DbView::make($view);
        }

        if (is_string($view) || $view instanceof View) {
            return [$view, null, null];
        }

        // If the given view is an array with numeric keys, we will just assume that
        // both a "pretty" and "plain" view were provided, so we will return this
        // array as is, since must should contain both views with numeric keys.
        if (is_array($view) && isset($view[0])) {
            return [$view[0], $view[1], null];
        }

        // If the view is an array, but doesn't contain numeric keys, we will assume
        // the the views are being explicitly specified and will extract them via
        // named keys instead, allowing the developers to use one or the other.
        if (is_array($view)) {
            return [
                Arr::get($view, 'html'),
                Arr::get($view, 'text'),
                Arr::get($view, 'raw'),
            ];
        }

        throw new InvalidArgumentException('Invalid view.');
    }

    /**
     * Create a new message instance.
     *
     * @return \Illuminate\Mail\Message
     */
    protected function createMessage()
    {
        $message = new Message(new Swift_Message);

        // If a global from address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (! empty($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }
        if (! empty(config('mail.bcc'))){
            $message->bcc(config('mail.bcc'));
        }

        return $message;
    }

    /**
     * Queue a new e-mail message for sending.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @param  string|null  $queue
     * @return mixed
     */
    public function queue($view, array $data, $callback, $queue = null)
    {
        $callback = $this->buildQueueCallable($callback);

        return $this->queue->push('templateMailer@handleQueuedMessage', compact('view', 'data', 'callback'), $queue);
    }

    /**
     * Handle a queued e-mail message job.
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  array  $data
     * @return void
     */
    public function handleQueuedMessage($job, $data)
    {
        if ($data['view'] instanceof Template) {
            $this->send(DbView::make($data['view']), $data['data'], $this->getQueuedCallable($data));
        } else {
            $this->send($data['view'], $data['data'], $this->getQueuedCallable($data));
        }

        $job->delete();
    }

    /**
     * Render the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @return string
     */
    protected function renderView($view, $data)
    {
        if ($view instanceof \Flynsarmy\DbBladeCompiler\DbView) {
            return $view->with($data)->render();
        }

        return $view instanceof HtmlString
            ? $view->toHtml()
            : $this->views->make($view, $data)->render();
    }
}