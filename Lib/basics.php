<?php

function _d ($t)
{
 debug( Debugger::trace() . "\n<br /> ------------------ \n<br />\n");
 debug ($t);
 die ();
}
