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
     * @var string|null a string that should replace all newline characters
     * in a log message. Default ist `null` for no replacement.
     */
    public $replaceNewline;

    protected $fp;

    protected $openedFp = false;

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        if ($this->openedFp) {
            fclose($this->fp);
        }
    }

    /**
     * @inheritdoc
     */
    public function init() {
        if (empty($this->fp) && empty($this->url)) {
            throw new InvalidConfigException("Either 'url' or 'fp' mus be set.");
        }
    }

    /**
     * @param resource $value an open and writeable resource. This can also be
     * one of PHP's pre-defined resources like `STDIN` or `STDERR`, which are
     * available in CLI context.
     */
    public function setFp($value)
    {
        if (!is_resource($value)) {
            throw new InvalidConfigException("Invalid resource.");
        }
        $metadata = stream_get_meta_data($value);
        if (!in_array($metadata['mode'], ['w', 'wb', 'a', 'ab'])) {
            throw new InvalidConfigException("Resource is not writeable.");
        }
        $this->fp = $value;
    }

    /**
     * @return resource the stream resource to write messages to
     */
    public function getFp()
    {
        if ($this->fp===null) {
            $this->fp = @fopen($this->url,'w');
            if ($this->fp===false) {
                throw new InvalidConfigException("Unable to open '{$this->url}' for writing.");
            }
            $this->openedFp = true;
        }
        return $this->fp;
    }

    /**
     * Writes a log message to the given target URL
     * @throws InvalidConfigException if unable to open the stream for writing
     */
    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        fwrite($this->getFp(), $text);
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
