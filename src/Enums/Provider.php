<?php

namespace Stephenjude\PaymentGateway\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

enum Provider: string
{
    use InvokableCases;
    use Names;
    use Values;
    use Options;

    case STRIPE = 'stripe';

    case PAYSTACK = 'paystack';

    case PAY4ME = 'pay4me';

    case MONNIFY = 'monnify';

    case FLUTTERWAVE = 'flutterwave';

    case SEERBIT = 'seerbit';

    case KLASHA = 'klasha';
}
