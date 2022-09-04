Yii Framework 2 Symfony mailer extension Change Log
================================================

2.0.4 September 04, 2022
------------------------

- Enh #22: Added an exception if there is no transport configuration (Krakozaber)


2.0.3 February 10, 2022
-----------------------

- Bug #20: Remove final from Mailer and Message class (Krakozaber)


2.0.2 February 03, 2022
-----------------------

- Bug #15: Fix return value of Message::embed() and Message::embedContent() (Hyncica)
- Bug #17: Fix missing import for `\RuntimeException` in `Mailer` (samdark)
- Bug #18: Fix `Message` incompatibility with `MessageInterface` (samdark)
- Bug #18: Fix not calling `Message` constructor (samdark)


2.0.1 December 31, 2021
-----------------------

- Bug #12: Fix namespace import in Mailer.php (Krakozaber)


2.0.0 December 30, 2021
-----------------------

- Initial release.

