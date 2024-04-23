<?php

namespace Stephenjude\PaymentGateway\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;
use Stephenjude\PaymentGateway\Contracts\ProviderInterface;
use Stephenjude\PaymentGateway\Providers\FlutterwaveProvider;
use Stephenjude\PaymentGateway\Providers\KlashaProvider;
use Stephenjude\PaymentGateway\Providers\MonnifyProvider;
use Stephenjude\PaymentGateway\Providers\PawapayProvider;
use Stephenjude\PaymentGateway\Providers\Pay4meProvider;
use Stephenjude\PaymentGateway\Providers\PaystackProvider;
use Stephenjude\PaymentGateway\Providers\SeerbitProvider;
use Stephenjude\PaymentGateway\Providers\StartbuttonProvider;
use Stephenjude\PaymentGateway\Providers\StripeProvider;

enum Provider: string
{
    use InvokableCases;
    use Names;
    use Values;
    use Options;

    case STRIPE = 'stripe';

    case PAYSTACK = 'paystack';

    case STARTBUTTON = 'startbutton';

    case PAY4ME = 'pay4me';

    case MONNIFY = 'monnify';

    case FLUTTERWAVE = 'flutterwave';

    case SEERBIT = 'seerbit';

    case KLASHA = 'klasha';

    case PAWAPAY = 'pawapay';

    public static function integration(string $provider): ProviderInterface
    {
        return match ($provider) {
            self::PAYSTACK() => new PaystackProvider(),
            self::STARTBUTTON() => new StartbuttonProvider(),
            self::FLUTTERWAVE() => new FlutterwaveProvider(),
            self::PAY4ME() => new Pay4meProvider(),
            self::SEERBIT() => new SeerbitProvider(),
            self::MONNIFY() => new MonnifyProvider(),
            self::STRIPE() => new StripeProvider(),
            self::KLASHA() => new KlashaProvider(),
            self::PAWAPAY() => new PawapayProvider(),
            default => throw new \RuntimeException("Undefined provider [$provider] called.")
        };
    }

    public function gateway(): ProviderInterface
    {
        return self::integration($this->value);
    }
}
