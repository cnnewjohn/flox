<?php
/**
 * Flox Engine Core Class
 */
class Flox_Core
{
    private $_flow;

    public static function factory($flow_id, $config = array())
    {
        return new self($flow_id, $config);
    }

    public function __construct($flow_id, $config)
    {
        $this->_flow = Flox_Flow::factory($flow_id, $config);
    }

    public function execute($arg = array())
    {
        $this->_flow->execute($arg);
    }

}
