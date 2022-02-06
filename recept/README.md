# Recept (a.k.a. *Het Kookpunt*)

A PHP application for keeping recipes on the web. Most of the application was written between 2005 and 2007 under PHP version 4 and 5. It is probably compatible up to PHP version 5.7, but would have issues under higher versions.

Since this was targeted at a Dutch-speaking audience, translation to other languages is required if an international audience is targeted.

The current version is highly incompatible with modern PHP standards. Some small changes were made to the `classes/Dbase.php` database class to support `mysqli` commands instead of the `php-mysql` from the original version. All admin/editing pages and the `Visitor`/`Top10` code was disabled because there is a lot of `php-mysql` code in there.
