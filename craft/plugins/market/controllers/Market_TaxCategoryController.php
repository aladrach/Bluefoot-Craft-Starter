<?php
namespace Craft;

/**
 * Class Market_TaxCategoryController
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2015, Pixel & Tonic, Inc.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @see       http://buildwithcraft.com/commerce
 * @package   craft.plugins.commerce.controllers
 * @since     1.0
 */
class Market_TaxCategoryController extends Market_BaseController
{
	/**
	 * @throws HttpException
	 */
	public function actionIndex()
	{
		$this->requireAdmin();

		$taxCategories = craft()->market_taxCategory->getAll();
		$this->renderTemplate('market/settings/taxcategories/index', compact('taxCategories'));
	}

	/**
	 * Create/Edit Tax Category
	 *
	 * @param array $variables
	 *
	 * @throws HttpException
	 */
	public function actionEdit(array $variables = [])
	{
		$this->requireAdmin();

		if (empty($variables['taxCategory'])) {
			if (!empty($variables['id'])) {
				$id                       = $variables['id'];
				$variables['taxCategory'] = craft()->market_taxCategory->getById($id);

				if (!$variables['taxCategory']) {
					throw new HttpException(404);
				}
			} else {
				$variables['taxCategory'] = new Market_TaxCategoryModel();
			};
		}

		if (!empty($variables['id'])) {
			$variables['title'] = $variables['taxCategory']->name;
		} else {
			$variables['title'] = Craft::t('Create a Tax Category');
		}

		$this->renderTemplate('market/settings/taxcategories/_edit', $variables);
	}

	/**
	 * @throws HttpException
	 */
	public function actionSave()
	{
		$this->requireAdmin();
		$this->requirePostRequest();

		$taxCategory = new Market_TaxCategoryModel();

		// Shared attributes
		$taxCategory->id          = craft()->request->getPost('taxCategoryId');
		$taxCategory->name        = craft()->request->getPost('name');
		$taxCategory->code        = craft()->request->getPost('code');
		$taxCategory->description = craft()->request->getPost('description');
		$taxCategory->default     = craft()->request->getPost('default');

		// Save it
		if (craft()->market_taxCategory->save($taxCategory)) {
			craft()->userSession->setNotice(Craft::t('Tax category saved.'));
			$this->redirectToPostedUrl($taxCategory);
		} else {
			craft()->userSession->setError(Craft::t('Couldn’t save tax category.'));
		}

		// Send the tax category back to the template
		craft()->urlManager->setRouteVariables([
			'taxCategory' => $taxCategory
		]);
	}

	/**
	 * @throws HttpException
	 */
	public function actionDelete()
	{
		$this->requireAdmin();
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$id = craft()->request->getRequiredPost('id');

		craft()->market_taxCategory->deleteById($id);
		$this->returnJson(['success' => true]);
	}

}