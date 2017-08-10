<?php

namespace wcf\action;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\payment\type\IPaymentType;

require_once(WCF_DIR . 'lib/data/paymentwall/paymentwall.php');

class PMWCallbackAction extends AbstractAction {

    /**
     * @inheritdoc
     */
    public function execute() {
        parent::execute();

        \Paymentwall_Config::getInstance()->set([
            'api_type' => \Paymentwall_Config::API_GOODS,
            'public_key' => PAYMENTWALL_PROJECT_KEY,
            'private_key' => PAYMENTWALL_PROJECT_SECRET
        ]);

        try {

            if (strpos($_SERVER['REQUEST_URI'], 'index.php') === false) {
                unset($_GET['pmw-callback/']);
            }
            unset($_GET['controller']);

            $pingback = new \Paymentwall_Pingback($_GET, $_SERVER['REMOTE_ADDR']);
            if ($pingback->validate()) {
                if ($pingback->isDeliverable()) {
                    $status = 'completed';
                } else if ($pingback->isCancelable()) {
                    $status = 'reversed';
                }

                $token = explode(':', $pingback->getParameter('wcfToken'), 2);

                $objectType = ObjectTypeCache::getInstance()->getObjectType(intval($token[0]));
                if ($objectType === null || !($objectType->getProcessor() instanceof IPaymentType)) {
                    throw new SystemException('invalid payment type id');
                }
                $processor = $objectType->getProcessor();

                $transactionDetails = array_merge(['productID' => $pingback->getProduct()->getId()], $_GET);

                if ($status) {
                    $processor->processTransaction(ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.payment.method', 'de.isekaidev.payment.method.pmw'), $token[1], $transactionDetails['wcfAmount'], $transactionDetails['wcfCurrency'], $transactionDetails['productID'], $status, $transactionDetails);
                    echo 'OK';
                }
            } else {
                echo $pingback->getErrorSummary();
            }
        } catch (SystemException $e) {
            @header('HTTP/1.1 500 Internal Server Error');
            echo $e->getMessage();
            exit;
        }
    }

}
