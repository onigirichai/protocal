<?php


class SSHTunnel{

    private static $pids = array();
    private static $cmd;

    public static function start(){
        self::$cmd = "ssh -N -L 3307:192.168.100.13:3306 ladev@133.5.19.111 -i \pwd\ladev_privatekey  -oStrictHostKeyChecking=no";
        echo "SSHトンネリングを開始します。".PHP_EOL;
        exec( self::$cmd ,$output ,$return_var );
        if( $return_var != 0) throw new Exception( $output[0] );
        self::set_pid();
    }

    private function set_pid(){
        $_cmd = "ps aux | grep '[0-9] ".self::$cmd."' | awk '{print $2}'";
        exec( $_cmd ,$outputs ,$return_var );
        if( $return_var != 0) throw new Exception( $outputs[0] );
        foreach ((array)$outputs as $output) {
            self::$pids[] = $output;
        }
    }

    public static function stop(){

        foreach (self::$pids as $pid) {
            $_cmd = 'kill '.$pid;
            exec( $_cmd ,$output ,$return_var );
            if( $return_var != 0) throw new Exception( $output[0] );
        }
    }
}