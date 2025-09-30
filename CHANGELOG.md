# Changelog

All notable changes to `laravel-payment-gateways` will be documented in this file.

## 3.0.14 - 2025-09-30

### What's Changed

* Improve error messages and support contact text by @stephenjude in https://github.com/stephenjude/laravel-payment-gateways/pull/27

**Full Changelog**: https://github.com/stephenjude/laravel-payment-gateways/compare/3.0.13...3.0.14

## 3.0.13 - 2025-09-30

- Include ‘Complete’ in successful checks. by @stephenjude

**Full Changelog**: https://github.com/stephenjude/laravel-payment-gateways/compare/3.0.12...3.0.13

## 3.0.12 - 2025-09-11

### What's Changed

* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/stephenjude/laravel-payment-gateways/pull/26

**Full Changelog**: https://github.com/stephenjude/laravel-payment-gateways/compare/3.0.11...3.0.12

## 3.0.11 - 2025-06-16

### What's Changed

* clean up channel checks for Startbutton integration by @stephenjude in https://github.com/stephenjude/laravel-payment-gateways/pull/23
* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot in https://github.com/stephenjude/laravel-payment-gateways/pull/24
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot in https://github.com/stephenjude/laravel-payment-gateways/pull/25

**Full Changelog**: https://github.com/stephenjude/laravel-payment-gateways/compare/3.0.10...3.0.11

## 3.0.10 - 2025-04-29

- Clean up channel checks for Startbutton [#96010](https://github.com/stephenjude/laravel-payment-gateways/commit/96010a342a5588eb2cbbaeb1d5a1394494fd6ed9)

**Full Changelog**: https://github.com/stephenjude/laravel-payment-gateways/compare/3.0.9...3.0.10

## 3.0.9 - 2025-04-21

### What's Changed

* Add return types to payment provider interface by @stephenjude in https://github.com/stephenjude/laravel-payment-gateways/pull/22

### New Contributors

* @stephenjude made their first contribution in https://github.com/stephenjude/laravel-payment-gateways/pull/22

**Full Changelog**: https://github.com/stephenjude/laravel-payment-gateways/compare/3.0.8...3.0.9

## 3.0.8 - 2025-04-21

### What's Changed

* hotfix: metadata is not always present by @eyounelson in https://github.com/stephenjude/laravel-payment-gateways/pull/21

**Full Changelog**: https://github.com/stephenjude/laravel-payment-gateways/compare/3.0.7...3.0.8

## 3.0.7 - 2025-02-01

- Removed debug statement by @eyounelson  in #20

## 3.0.6 - 2025-01-27

- Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 in #19

## 3.0.5 - 2024-09-02

- Bump deps

## 3.0.4 - 2024-06-07

- Make debug mode Laravel compliant.
- Make Startbutton's USD default payment channel null

## 3.0.3 - 2024-06-04

- Make Startbutton `partner` parameter optional

## 3.0.2 - 2024-05-10

- Fixed Monnify payment status and verification by @stephenjude
- Add partner parameter and default partner for Startbutton integration by @stephenjude

## 3.0.1 - 2024-05-08

- Fixed Klasha initialization error by @stephenjude

## 3.0.0 - 2024-04-24

- Integrate Startbutton APIs
- Laravel 11 compatibility.

## 2.2.1 - 2024-01-23

- Fixed payment confirmation for Seerbit.

## 2.2.0 - 2023-11-21

- Pawapay integration.

## 2.1.2 - 2023-10-02

- Changed transaction metadata to mixed type.

## 2.1.1 - 2023-09-22

- Added metadata to Monnify payment initialization by @Official-David

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
