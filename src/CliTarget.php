<?php
namespace codemix\streamlog;

use yii\log\Target as BaseTarget;
use yii\base\InvalidConfigException;

/**
 * A log target for pre-opened stream resources.
 *
 * Recommended use is against STDERR or STDOUT in PHP-CLI applications.
 *
 * @property-write resource $fp Pre-open writable stream resource.
 */
class CliTarget extends BaseTarget
{

    /**
     * @var string|null a string that should replace all newline characters in a log message.
     * Default ist `null` for no replacement.
     */
    public $replaceNewline;

    /**
     * Already open writable stream to send logs entries to.
     *
     * Recommended for use in CLI context for STDOUT and STDERR.
     * Property 'url' takes precedence.
     *
     * @var resource
     */
    private $_fp = null;


    /**
     * The writable stream resource instance managed by the target.
     *
     * @return resource
     */
    protected function getFp() {
        return $this->_fp;
    }

    /**
     * Mutator of `fp` property.
     *
     * @param resource $fp
     * @throws InvalidConfigException
     */
    public function setFp(resource $fp) {
        $metadata = stream_get_meta_data($fp);
        if ($metadata['mode'] != 'w') {
            throw new InvalidConfigException("Non-writable stream provided!");
        }
        $this->_fp = $fp;
    }

    /**
     * Validates class configuration.
     *
     * Ensures that a writable stream is present in the system, based on config.
     *
     * @throws InvalidConfigException When unable to aquire a writable stream.
     */
    public function init() {
        // We should have a writable stream available at this point.
        if (empty($this->_fp)) {
            throw new InvalidConfigException("No writable stream provided! Set `fp` property.");
        }
    }

    /**
     * Writes a log message to the given target URL.
     */
    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        fwrite($this->_fp, $text);
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
}
