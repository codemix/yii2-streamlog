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
     * Writes a log message to the given target URL
     * @throws InvalidConfigException if unable to open the stream for writing
     */
    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        if (empty($this->url)) {
            throw new InvalidConfigException("No url configured.");
        }
        if (($fp = @fopen($this->url,'w')) === false) {
            throw new InvalidConfigException("Unable to append to '{$this->url}'");
        }
        fwrite($fp, $text);
        fclose($fp);
    }
}
