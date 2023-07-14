<?php

namespace Enums;

enum TransitionAction
{
    case Shift;
    case Reduce;
    case Accept;
}