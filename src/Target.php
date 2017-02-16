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
     * @var string the URL to use. See http://php.net/manual/en/wrappers.php
     * for details. This gets ignored if [[fp]] is configured.
     */
    public $url;

    /**
     * @var resource an open and writeable resource. This can also be one of
     * PHP's pre-defined resources like `STDIN` or `STDERR`, which are available
     * in CLI context.
     */
    public $fp;

    /**
     * @var string|null a string that should replace all newline characters
     * in a log message. Default ist `null` for no replacement.
     */
    public $replaceNewline;

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        if (!empty($this->url)) {
            fclose($this->fp);
        }
    }

    /**
     * @inheritdoc
     */
    public function init() {
        if (empty($this->fp)) {
            if (empty($this->url)) {
                throw new InvalidConfigException("Either 'url' or 'fp' mus be set.");
            }
            $this->fp = @fopen($this->url,'w');
            if ($this->fp===false) {
                throw new InvalidConfigException("Unable to open '{$this->url}' for writing.");
            }
        } else {
            if (!is_resource($this->fp)) {
                throw new InvalidConfigException("Invalid resource.");
            }
            $metadata = stream_get_meta_data($this->fp);
            if ($metadata['mode']!=='w') {
                throw new InvalidConfigException("Resource is not writeable.");
            }
        }
    }

    /**
     * Writes a log message to the given target URL
     * @throws InvalidConfigException if unable to open the stream for writing
     */
    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        fwrite($this->fp, $text);
    }

    /**
     * @inheritdoc
     */
    public function formatMessage($message)
    {
        $text = parent::formatMessage($message);
        if ($this->replaceNewline===null) {
            return $text;
        } else {
            return str_replace("\n", $this->replaceNewline, $text);
        }
    }
}
