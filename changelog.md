# Change log

## [Unreleased]

- Normalizing of responder input.
- Wrapping known exceptions in service-specific exceptions.

## Version 0.8.0

- Complete refactoring of the available services. This is very much a backwards-incompatible update.

## Version 0.3.1

### Fixed

- Added proper supporting of string-based QR codes. Only permanent QR codes can have a string format - temporary codes can only be integers.

## Version 0.3.0

### Added

- Change log
- Support for PSR-7 responses in the responder.

### Changed

- Sending of replies in the responder are now done by returning the message to send from the event listener. 

## Version 0.2.0

### Changed

- Moved the namespace from `\WeChat` to `\Garbetjie\WeChatClient`, to prevent accidental namespace clashes.

## Version 0.1.1

### Fixed

- Non-JSON responses were being parsed as JSON, and causing failures ([#1](https://github.com/garbetjie/wechat-php/issues/1)).

## Version 0.1.0

Initial release.
