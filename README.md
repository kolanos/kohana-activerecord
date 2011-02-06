# kohana-activerecord

[php-activerecord](http://phpactiverecord.org) module for [Kohana](http://kohanaframework.org)

## Example

    $a = User::first();
    or
    $a = Arm::factory('user')->first();
    
    echo $a->username.' => '.$a->roles[0]->name;
