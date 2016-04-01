<?php

class wTimer
{


    function wTimer($total)
    {
        $this->checked = 0;
        $this->start = microtime(true);
        $this->total = $total;
        $this->tick = $this->start;
    }

    function incChecked()
    {
        $tickSec = 10;

        $now = microtime(true);
        $diff = $now - $this->tick;
        if ($diff >= $tickSec) {
            $this->tick = $now;
            $this->dumpStat();
        }


        ++$this->checked;
    }

    function dumpStat()
    {
        $cur = microtime(true);
        $work = round($cur - $this->start, 2);
        $pps = round($this->checked / $work, 2);
        $checkPer = round($this->checked / $this->total * 100, 2);
        $toCheck = $this->total - $this->checked;

        $leftSec = $toCheck / $pps;
        $eta = gmdate('H:i:s', $leftSec);

        sout("time of work: $work sec, pwds checked: $this->checked ($checkPer%), pwds per sec: $pps, time left: $eta");
    }

    function formatHMS($time)
    {
        $s = $time % 60;
        $time = floor($time / 60);

        $m = $time % 60;
        $time = floor($time / 60);

        $h = floor($time);

        $str = $s;

        if ($m > 0)
            $str = "$m min:$str";
        if ($h > 0)
            $str = "$h hr:$str";

        return $str;

    }
}

?>