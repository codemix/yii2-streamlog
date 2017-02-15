<?php
namespace codemix\streamlog;

use yii\base\InvalidConfigException;

/**
 * A log target for streams in URL format.
 */
class Target extends CliTarget
{
    /**
     * @var string the URL to use. See http://php.net/manual/en/wrappers.php for details.
     */
    public $url;

    /**
     * Validates class configuration.
     *
     * Ensures that a writable stream is present in the system, based on config.
     *
     * @throws InvalidConfigException When unable to aquire a writable stream.
     */
    public function init() {
        if (empty($this->url)) {
            throw new InvalidConfigException("No url configured.");
        }
        if (($fp = @fopen($this->url, 'w')) === false) {
            throw new InvalidConfigException("Unable to append to '{$this->url}'");
        }
        $this->setFp($fp);
    }

    /**
     * Clean-up streams that we've opened.
     */
    public function __destruct() {
        fclose($this->getFp());
    }
}
