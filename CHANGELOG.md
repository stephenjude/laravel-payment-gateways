# Changelog

All notable changes to `laravel-payment-gateways` will be documented in this file.

## 2.1.0 - 2023-09-22

- Added custom routes for successful and failed payments.
- Added transaction find & list method for Pay4Me, Paystack, Flutterwave, Monnify, and Stripe. SEERBIT & Klasha supports only find.
- Fixed Monnify payment confirmation.
- Map error response from supported providers.
- Updated SEERBIT payment integration.
- Update API URL usage.

## 2.0.8 - 2023-06-21

- Fixed API URL path usage

## 2.0.7 - 2023-06-20

- Update API URL usage

## 2.0.6 - 2023-05-11

- Added Seerbit `approved` status code
- Removed debug statement
- Clean up

## 2.0.5 - 2023-05-06

- Switch to Monnify live API

## 2.0.4 - 2023-05-06

- Monnify integration 🔥

## 2.0.3 - 2023-04-25

- SEERBIT Integration

## 2.0.2 - 2023-04-13

- FEATURE: Add custom route configs for successful and failed payments.

## 2.0.1 - 2023-04-04

- Allow html contents on configurable gateway messages

## 2.0.0 - 2023-03-07

- Laravel 10 support

## 1.0.18 - 2023-03-07

- Laravel 10 support

## 1.0.17 - 2023-03-05

- Clean up

## 1.0.16 - 2023-03-05

- Clean up

## 1.0.15 - 2023-03-05

- Stripe payment verification

## 1.0.14 - 2023-03-05

- Fixed stripe amount parameter.
- WIP: Seerbit integration.

## 1.0.13 - 2023-01-27

- Add gateway error to exception.

## 1.0.12 - 2023-01-09

- Fixed dynamic support email - #6

## 1.0.11 - 2023-01-05

- Fixed typographical errors.
- Fixed Pay4Me verification error.

## 1.0.10 - 2023-01-05

- Update: Pay4Me provider default Api URL

## 1.0.9 - 2023-01-04

- UPDATE: Pay4Me provider

## 1.0.8 - 2023-01-04

- Clean up
- Fixed test workflow

## 1.0.7 - 2023-01-04

- Fixed reflection error for Pay4Me provider.

## 1.0.6 - 2023-01-04

- Fixed reflection error for Pay4Me provider.

## 1.0.5 - 2023-01-04

- Pay4Me Pay integration.

## 1.0.4 - 2022-11-06

- Fixed stripe amount conversion error

## 1.0.3 - 2022-11-06

- Fixed decimal value error

## 1.0.2 - 2022-11-06

- Fixed amount calculation error.

## 1.0.1 - 2022-11-06

- Fixed stripe initialization error due to decimal values.

## 1.0.0 - 2022-08-27

- Ready for production use.
- Fixed failing tests.
- Updated docs.

## 0.1.8 - 2022-08-25

- Fix named parameter error.

## 0.1.7 - 2022-08-11

- Klasha payment gateway integration.
- Improved the fallback error page.

## 0.1.6 - 2022-08-08

- Add callback option for custom code execution after customer payment.
- Parse error messages.
- Add processing status message for delayed confirmation.
- Code improvements.

## 0.1.5 - 2022-07-04

- Bump dependabot/fetch-metadata from 1.3.1 to 1.3.3

## 0.0.4 - 2022-06-14

I would have tagged this significant release with lots of breaking changes, but thanks to God, we still don't have a stable release.

In this release, I reworked the hosted providers to generate the checkout URL from the gateway providers.

Completed the implementation for the following providers:

- Paystack 🔥
- Flutterwave 🔥
- Stripe 🔥

That's all for now, more implementation is coming for Monnify and Paypal 🚀

## 0.1.3 - 2022-06-02

- Increase default timeout to 12 hours

## 0.1.2 - 2022-05-31

- Fix missing debug response

## 0.1.1 - 2022-05-31

- Fix missing debug response

## 0.1.0 - 2022-05-31

- Fix missing debug response

## 0.0.9 - 2022-05-31

- Fix debug mode error

## 00.7 - 2022-05-31

- Updated GitHub Actions
- Updated Readme

## 0.0.6 - 2022-05-31

- Fix config file publishing error

## 0.0.5 - 2022-05-31

Added debug mode for logging HTTP responses.

## 0.0.3 - 2022-05-26

- Allow `null` value for `$channel` property

## 0.0.2 - 2022-05-25

- Clean up
