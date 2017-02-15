<?php
namespace codemix\streamlog;

use yii\log\Target as BaseTarget;
use yii\base\InvalidConfigException;

/**
 * A log target for streams in URL format.
 */
class Target extends BaseTarget
{
    /**
     * @var string the URL to use. See http://php.net/manual/en/wrappers.php for details.
     */
    public $url;

    /**
     * Already open writable stream to send logs entries to.
     *
     * Recommended for use in CLI context for STDOUT and STDERR.
     * Property 'url' takes precedence.
     *
     * @var resource
     */
    public $fp = null;

    /**
     * @var string|null a string that should replace all newline characters in a log message.
     * Default ist `null` for no replacement.
     */
    public $replaceNewline;

    /**
     * Validates class configuration.
     *
     * Ensures that a writable stream is present in the system, based on config.
     *
     * @throws InvalidConfigException When unable to aquire a writable stream.
     */
    public function init() {
        // URL overwrites the passed on stream and is being explicitly opened.
        if (!empty($this->url) && ($this->fp = @fopen($this->url, 'w')) !== false) {
            return;
        }

        // We should have a writable stream passed in.
        if (!empty($this->fp)
            && is_resource($this->fp)
            && ($metadata = stream_get_meta_data($this->fp))
            && $metadata['mode'] == 'w'
        ) {
            return;
        }

        throw new InvalidConfigException("No writable stream aquired! Set `url` or `fp` properties.");
    }

    /**
     * Writes a log message to the given target URL.
     */
    public function export()
    {
        $callback = [$this, 'formatMessage'];
        $text = implode("\n", array_map($callback, $this->messages)) . "\n";
        fwrite($this->fp, $text);
    }

    /**
     * @inheritdoc
     */
    public function formatMessage($message)
    {
        $text = parent::formatMessage($message);
        if ($this->replaceNewline !== null) {
            $text = str_replace("\n", $this->replaceNewline, $text);
        }
        return $text;
    }

    /**
     * Clean-up streams that we've opened.
     */
    public function __destruct() {
        if (!empty($this->url) && $this->fp) {
            fclose($this->fp);
        }
    }
}
