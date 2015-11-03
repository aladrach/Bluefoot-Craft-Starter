<?PHP
/**
 * Venti by TippingMedia
 *
 * @package   Venti
 * @author    Adam Randlett
 * @copyright Copyright (c) 2014, TippingMedia
 */

namespace Craft;


class Venti_AjaxController extends BaseController
{

    
    /*
     * Render repeat date modal from ajax call.
     */
    public function actionModal()
    {
        $this->requireAjaxRequest();
        $this->renderTemplate('venti/_modal');
    }

}
?>