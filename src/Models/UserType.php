<?php

namespace Woodlands\Core\Models;

enum UserType: string
{
    case Staff = "staff";
    case Student = "student";
};
