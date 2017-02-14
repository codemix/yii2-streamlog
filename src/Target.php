<?php
namespace codemix\streamlog;

use yii\log\Target as BaseTarget;
use yii\base\InvalidConfigException;

/**
 * A log target for streams in URL format.
 */
class Target extends BaseTarget
{
    private static $_instances = 0;
    private static $_allHandles = [];
    private static $_isCli = false;

    /**
     * Instance handle.
     *
     * Used to spare the array-look-up in the shared static pool.
     *
     * @var resource
     */
    private $_handle = null;

    /**
     * @var string the URL to use. See http://php.net/manual/en/wrappers.php for details.
     */
    public $url;

    /**
     * @var string|null a string that should replace all newline characters in a log message.
     * Default ist `null` for no replacement.
     */
    public $replaceNewline;

    /**
     * Init component's file handle and validate the passed configuration.
     *
     * @throws InvalidConfigException
     *   When no URL is configured or unable to open the stream for writing.
     */
    public function init() {
        // Add a new target instance flag.
        ++self::$_instances;

        // Validate config.
        if (empty($this->url)) {
            throw new InvalidConfigException("No url configured.");
        }

        // Sniff-out the CLI-defined output constants.
        // http://php.net/manual/en/features.commandline.io-streams.php
        if (defined('STDOUT') || defined('STDERR')) {
            self::$_isCli = true;
            self::$_allHandles += ['php://stdout' => STDOUT];
            self::$_allHandles += ['php://stderr' => STDERR];
        }

        // Reuse already opened streams for the URL to spare some I/O.
        if (isset(self::$_allHandles[$this->url])) {
            $this->_handle = self::$_allHandles[$this->url];
            return;
        }

        // Try to open a new stream.
        if (($this->_handle = @fopen($this->url,'w')) === false) {
            throw new InvalidConfigException("Unable to append to '{$this->url}'");
        }

        // Allow others to reuse it.
        self::$_allHandles[$this->url] = $this->_handle;
    }

    /**
     * Writes a log message to the given target URL
     */
    public function export()
    {
        $callback = [$this, 'formatMessage'];
        $text = implode("\n", array_map($callback, $this->messages)) . "\n";
        fwrite($this->_handle, $text);
    }

    /**
     * Clean-up.
     */
    public function __destruct() {
        // Clean-up handles when the last instance is destroyed.
        if (--self::$_instances > 0) {
          return;
        }

        // For CLI context they were already opened, so skip closing them.
        if (self::$_isCli) {
          unset(self::$_allHandles['php://stdout']);
          unset(self::$_allHandles['php://stderr']);
        }

        // Clear all opened streams.
        foreach (self::$_allHandles as $handle) {
          fclose($handle);
        }
        self::$_allHandles = [];
    }

    /**
     * @inheritdoc
     */
    public function formatMessage($message)
    {
        $text = parent::formatMessage($message);
        return $this->replaceNewline===null ? $text : str_replace("\n", $this->replaceNewline, $text);
    }
}
