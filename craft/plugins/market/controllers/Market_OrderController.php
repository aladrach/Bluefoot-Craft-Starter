<?php
namespace Craft;

/**
 * Class Market_OrderController
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2015, Pixel & Tonic, Inc.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @see       http://buildwithcraft.com/commerce
 * @package   craft.plugins.commerce.controllers
 * @since     1.0
 */
class Market_OrderController extends Market_BaseController
{
    protected $allowAnonymous = false;

    /**
     * Index of orders
     */
    public function actionOrderIndex()
    {
        $this->requireAdmin();

        // Remove all incomplete carts older than a certain date in config.
        craft()->market_cart->purgeIncompleteCarts();

        $this->renderTemplate('market/orders/_index');
    }

    /**
     * @param array $variables
     *
     * @throws HttpException
     */
    public function actionEditOrder(array $variables = [])
    {
        $this->requireAdmin();

        $variables['orderSettings'] = craft()->market_orderSettings->getByHandle('order');

        if (empty($variables['order'])) {
            if (!empty($variables['orderId'])) {
                $variables['order'] = craft()->market_order->getById($variables['orderId']);

                if (!$variables['order']->id) {
                    throw new HttpException(404);
                }
            } else {
                $variables['order']         = new Market_OrderModel();
            };
        }

        if (!empty($variables['orderId'])) {
            $variables['title'] = "Order " . substr($variables['order']->number,0,7);
        } else {
            $variables['title'] = Craft::t('Create a new Order');
        }

        $variables['orderStatuses'] = \CHtml::listData(craft()->market_orderStatus->getAll(),
            'id', 'name');
        if ($variables['order']->orderStatusId == null) {
            $variables['orderStatuses'] = ['0' => 'No Status'] + $variables['orderStatuses'];
        }

        $this->prepVariables($variables);

        $this->renderTemplate('market/orders/_edit', $variables);
    }

    /**
     * Modifies the variables of the request.
     *
     * @param $variables
     */
    private function prepVariables(&$variables)
    {
        $variables['tabs'] = [];

        foreach ($variables['orderSettings']->getFieldLayout()->getTabs() as $index => $tab) {
            // Do any of the fields on this tab have errors?
            $hasErrors = false;

            if ($variables['order']->hasErrors()) {
                foreach ($tab->getFields() as $field) {
                    if ($variables['order']->getErrors($field->getField()->handle)) {
                        $hasErrors = true;
                        break;
                    }
                }
            }

            $variables['tabs'][] = [
                'label' => Craft::t($tab->name),
                'url'   => '#tab' . ($index + 1),
                'class' => ($hasErrors ? 'error' : null)
            ];
        }
    }

    /**
     * Capture Transaction
     */
    public function actionTransactionCapture()
    {
        $this->requireAdmin();

        $id          = craft()->request->getParam('id');
        $transaction = craft()->market_transaction->getById($id);

        if ($transaction->canCapture()) {
            // capture transaction and display result
            $child = craft()->market_payment->captureTransaction($transaction);

            $message = $child->message ? ' (' . $child->message . ')' : '';

            if ($child->status == Market_TransactionRecord::SUCCESS) {
                craft()->market_order->updateOrderPaidTotal($child->order);
                craft()->userSession->setNotice(Craft::t('Transaction has been successfully captured: ') . $message);
            } else {
                craft()->userSession->setError(Craft::t('Capturing error: ') . $message);
            }
        } else {
            craft()->userSession->setError(Craft::t('Wrong transaction id'));
        }
    }

    /**
     * Refund Transaction
     */
    public function actionTransactionRefund()
    {
        $this->requireAdmin();

        $id          = craft()->request->getParam('id');
        $transaction = craft()->market_transaction->getById($id);

        if ($transaction->canRefund()) {
            // capture transaction and display result
            $child = craft()->market_payment->refundTransaction($transaction);

            $message = $child->message ? ' (' . $child->message . ')' : '';

            if ($child->status == Market_TransactionRecord::SUCCESS) {
                craft()->userSession->setNotice(Craft::t('Transaction has been successfully refunded: ') . $message);
            } else {
                craft()->userSession->setError(Craft::t('Refunding error: ') . $message);
            }
        } else {
            craft()->userSession->setError(Craft::t('Wrong transaction id'));
        }
    }

    /**
     *
     * @throws Exception
     * @throws HttpException
     * @throws \Exception
     */
    public function actionSaveOrder()
    {
        $this->requireAdmin();

        $this->requirePostRequest();

        $order = $this->_setOrderFromPost();
        $this->_setContentFromPost($order);

        if (craft()->market_order->save($order)) {
            $this->redirectToPostedUrl($order);
        }

        craft()->userSession->setNotice(Craft::t("Couldn't save order."));
        craft()->urlManager->setRouteVariables([
            'order' => $order
        ]);
    }

    /**
     * @return Market_OrderModel
     * @throws Exception
     */
    private function _setOrderFromPost()
    {
        $orderId = craft()->request->getPost('orderId');

        if ($orderId) {
            $order = craft()->market_order->getById($orderId);

            if (!$order) {
                throw new Exception(Craft::t('No order with the ID “{id}”',
                    ['id' => $orderId]));
            }
        } else {
            $order = new Market_OrderModel();
        }

        $orderStatusId = craft()->request->getPost('orderStatusId');
        if ($orderStatusId == 0 || $orderStatusId == null) {
            $order->orderStatusId = null;
        } else {
            $order->orderStatusId = $orderStatusId;
        }

        $order->message = craft()->request->getPost('message');

        return $order;
    }

    /**
     * @param Market_OrderModel $order
     */
    private function _setContentFromPost($order)
    {
        $order->setContentFromPost('fields');
    }

    /**
     * Deletes a order.
     *
     * @throws Exception if you try to edit a non existing Id.
     */
    public function actionDeleteOrder()
    {
        $this->requireAdmin();

        $this->requirePostRequest();

        $orderId = craft()->request->getRequiredPost('orderId');
        $order   = craft()->market_order->getById($orderId);

        if (!$order) {
            throw new Exception(Craft::t('No order exists with the ID “{id}”.',
                ['id' => $orderId]));
        }

        if (craft()->market_order->delete($order)) {
            if (craft()->request->isAjaxRequest()) {
                $this->returnJson(['success' => true]);
            } else {
                craft()->userSession->setNotice(Craft::t('Order deleted.'));
                $this->redirectToPostedUrl($order);
            }
        } else {
            if (craft()->request->isAjaxRequest()) {
                $this->returnJson(['success' => false]);
            } else {
                craft()->userSession->setError(Craft::t('Couldn’t delete order.'));
                craft()->urlManager->setRouteVariables(['order' => $order]);
            }
        }
    }
}