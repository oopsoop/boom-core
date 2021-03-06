<?php

namespace BoomCMS\Chunk;

use BoomCMS\Foundation\Chunk\AcceptsHtmlString;
use Carbon\Carbon;
use Closure;
use DateTime;

class Timestamp extends BaseChunk
{
    use AcceptsHtmlString;

    /**
     * @var Closure
     */
    protected $closure;

    public static $defaultFormat = 'j F Y';
    public static $formats = [
        'j F Y',
        'j F Y H:i',
        'j F Y h:i A',
        'l j F Y',
        'l j F Y H:i',
        'l j F Y h:i A',
        'H:i',
        'h:i A',
    ];

    protected $defaultHtml = "<span class='b-chunk-timestamp'>{time}</span>";
    protected $formatIsEditable = true;

    /**
     * Define a closure to apply to the chunk content before rendering.
     *
     * @param Closure $closure
     *
     * @return $this
     */
    public function apply(Closure $closure): Timestamp
    {
        $this->closure = $closure;

        return $this;
    }

    protected function addContentToHtml($content): string
    {
        $html = $this->html ?: $this->defaultHtml;

        return str_replace('{time}', $content, $html);
    }

    public function attributes(): array
    {
        return [
            $this->attributePrefix.'timestamp'        => $this->getTimestamp(),
            $this->attributePrefix.'format'           => $this->getFormat(),
            $this->attributePrefix.'formatIsEditable' => (int) $this->formatIsEditable,
        ];
    }

    public function hasContent(): bool
    {
        return $this->getFormat() && $this->getTimestamp() > 0;
    }

    /**
     * @return DateTime
     */
    public function getDateTime(): DateTime
    {
        return (new DateTime())->setTimestamp($this->getTimestamp());
    }

    public function getFormat(): string
    {
        return $this->attrs['format'] ?? static::$defaultFormat;
    }

    /**
     * @return string
     */
    public function getFormattedString(): string
    {
        return $this->getLocalised()->format($this->getFormat());
    }

    /**
     * Timestamps are saved as UTC.
     *
     * Returns a Carbon object with timezone set to the configured local timezone
     *
     * @return Carbon
     */
    public function getLocalised(): Carbon
    {
        return Carbon::createFromTimestampUTC($this->getTimeStamp())->tz(config('app.timezone'));
    }

    public function getTimestamp(): int
    {
        if (isset($this->attrs['timestamp'])) {
            return (int) $this->attrs['timestamp'];
        }

        return 0;
    }

    public function setFormat($format): Timestamp
    {
        $this->formatIsEditable = false;
        $this->attrs['format'] = $format;

        return $this;
    }

    protected function show()
    {
        $content = (is_callable($this->closure)) ?
            call_user_func($this->closure, $this)
            : $this->getFormattedString();

        return $this->addContentToHtml($content);
    }

    protected function showDefault()
    {
        return $this->addContentToHtml($this->getPlaceholderText());
    }
}
