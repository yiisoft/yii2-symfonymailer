Yii Framework 2 Symfony mailer extension Change Log
================================================

4.0.0 Jan 29, 2024
------------------
- Enh: Use DI container for creating factories (sammousa)

4.0.0
------

- Enh #45: Include logger proxy as a dependency (sammousa)
- Enh #45: Drop support for end-of-life php versions 7.4 and 8.0 (sammousa)

3.1.0 under development
-----------------------

- Enh #52: Forward events to the Yii event system (sammousa)
- Enh #45: Added option to create transport from Dsn object (Swanty)
- Enh #50: Forward transport logs to the Yii Logger (sammousa) 
- Enh #49: Removed dependency on SymfonyMailer class (sammousa)

3.0.0 December 05, 2022
-----------------------

- Enh #22: Added an exception if there is no transport configuration (Krakozaber)
- Enh #27: Extensive rewrite to make it more statically typed and better testable (sammousa)


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
