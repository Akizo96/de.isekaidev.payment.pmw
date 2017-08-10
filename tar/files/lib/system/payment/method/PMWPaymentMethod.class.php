<?php

namespace wcf\system\payment\method;

use wcf\system\WCF;

require_once(WCF_DIR . 'lib/data/paymentwall/paymentwall.php');

class PMWPaymentMethod extends AbstractPaymentMethod {

    /**
     * @inheritdoc
     */
    public function supportsRecurringPayments() {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedCurrencies() {
        return [
            'USD', // U.S. Dollar
            'ISK', // Icelandic Krona
            'HKD', // Hong Kong Dollar
            'TWD', // Taiwan New Dollar
            'CHF', // Swiss Franc
            'EUR', // Euro
            'DKK', // Danish Krone
            'CLP', // Chilean Peso
            'CAD', // Canadian Dollar
            'CNY', // Chinese yuan
            'THB', // Thai Baht
            'AUD', // Australian Dollar
            'SGD', // Singapore Dollar
            'KRW', // South Korean won
            'JPY', // Japanese Yen
            'PLN', // Polish Zloty
            'GBP', // Pound Sterling
            'SEK', // Swedish Krona
            'NZD', // New Zealand Dollar
            'BRL', // Brazilian Real
            'RUB' // Russian Ruble
        ];
    }

    /**
     * @inheritdoc
     */
    public function getPurchaseButton($cost, $currency, $name, $token, $returnURL, $cancelReturnURL, $isRecurring = false, $subscriptionLength = 0, $subscriptionLengthUnit = '') {
        if (!defined('PAYMENTWALL_PROJECT_KEY') || !defined('PAYMENTWALL_PROJECT_SECRET') || strlen(PAYMENTWALL_PROJECT_KEY) === 0 || strlen(PAYMENTWALL_PROJECT_SECRET) === 0) {
            return false;
        }

        \Paymentwall_Config::getInstance()->set([
            'api_type' => \Paymentwall_Config::API_GOODS,
            'public_key' => PAYMENTWALL_PROJECT_KEY,
            'private_key' => PAYMENTWALL_PROJECT_SECRET
        ]);

        $prodID = explode(':', $token);

        if ($isRecurring && $subscriptionLength > 0 && $subscriptionLengthUnit != '') {
            switch ($subscriptionLengthUnit) {
                case 'D':
                    $periodLength = \Paymentwall_Product::PERIOD_TYPE_DAY;
                    break;
                case 'M':
                    $periodLength = \Paymentwall_Product::PERIOD_TYPE_MONTH;
                    break;
                case 'Y':
                    $periodLength = \Paymentwall_Product::PERIOD_TYPE_YEAR;
                    break;
            }

            $product = new \Paymentwall_Product('product_' . $prodID[0] . '_' . $prodID[2], $cost, $currency, $name, \Paymentwall_Product::TYPE_SUBSCRIPTION, $subscriptionLength, $periodLength, true);
        } else {
            $product = new \Paymentwall_Product('product_' . $prodID[0] . '_' . $prodID[2], $cost, $currency, $name, \Paymentwall_Product::TYPE_FIXED);
        }

        $widget = new \Paymentwall_Widget(WCF::getUser()->getUserID(), 'p4', [$product], [
            'email' => WCF::getUser()->email,
            'history[registration_date]' => WCF::getUser()->registrationDate,
            'wcfToken' => $token,
            'wcfAmount' => $cost,
            'wcfCurrency' => $currency
        ]);
        WCF::getTPL()->assign([
            'productID' => $prodID[0] . '_' . $prodID[2],
            'isRecurring' => $isRecurring,
            'widget' => $widget->getHtmlCode(['width' => 500, 'height' => 444])
        ]);

        return WCF::getTPL()->fetch('buttonPaymentPMW');
    }
}