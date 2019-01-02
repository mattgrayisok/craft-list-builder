<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Automatically generate social media posts when you perform actions within the Craft control panel.
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\controllers;

use mattgrayisok\listbuilder\ListBuilder;
use mattgrayisok\listbuilder\Enums;

use mattgrayisok\listbuilder\models\Signup;

use Craft;
use Yii;
use craft\web\Controller;
use craft\helpers\Assets;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class SignupController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['store'];

    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        $variables = [];
        //$variables['connections'] = Posttosocial::$plugin->connectionManager->getAllConnections();
        return $this->renderTemplate('list-builder/signups/index', $variables);
    }

    public function actionData()
    {
        $request = Craft::$app->getRequest();
        $draw = $request->getParam('draw');
        $start = $request->getParam('start');
        $length = $request->getParam('length');
        $search = $request->getParam('search');
        $order = $request->getParam('order');

        $data = ListBuilder::$plugin->signupManager->datatablesData($start, $length, $search, $order);

        $finalData = array_map(function($el){
            //Table colums
            return [
                $el->email,
                $this->fTime($el->dateCreated->getTimestamp()),
                ($el->consent == Enums::CONSENT__YES ? 'Yes' : ($el->consent == Enums::CONSENT__NO ? 'No' : 'Unknown')),
            ];
        }, $data['models']);

        return json_encode([
            'draw' => $draw,
            'recordsTotal' => $data['total'],
            'recordsFiltered' => $data['filteredTotal'],
            'data' => $finalData
        ]);
    }

    public function actionGraph()
    {
        $request = Craft::$app->getRequest();
        $data = ListBuilder::$plugin->signupManager->graphData();
        return json_encode($data);
    }

    /**
     * @return mixed
     */
    public function actionStore()
    {
        //Storing a new mailing list signup
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $email = $request->getParam('email');
        $consent = $request->getParam('consent');
        $shortcode = $request->getParam('source');

        $source = ListBuilder::$plugin->sourceManager->getSourceByShortcode($shortcode);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->_returnSignupError($source, 'Invalid email address.');
        }

        $signup = new Signup();
        $signup->email = $email;

        $consentVal = Enums::CONSENT__UNKNOWN;

        if($consent === '0' || $consent === 'no'){
            $consentVal = Enums::CONSENT__NO;
            //If allow no consent is false, throw an error.
            //If no valid source is set default to error.
            if(
                is_null($source) ||
                is_null($source->getConfigValue('allownoconsent')) ||
                $source->getConfigValue('allownoconsent') == false
            ){
                return $this->_returnSignupError($source, 'Please check the consent box.');
            }
        }else if($consent === '1' || $consent === 'yes'){
            $consentVal = Enums::CONSENT__YES;
        }

        $signup->consent = $consentVal;

        if(is_null($source)){
            $signup->sourceId = null;
        }else{
            $signup->sourceId = $source->id;
        }

        $result = ListBuilder::$plugin->signupManager->storeSignup($signup);

        if (!$result) {
            return $this->_returnSignupError($source, 'A problem occured during subscription.');
            //Craft::$app->getSession()->setError(Craft::t('list-builder', 'Unable to process signup.'));
            //Craft::$app->getUrlManager()->setRouteParams([
                //'signup' => $signup,
            //]);
            //return null;
        }

        return $this->_returnSignupSuccess($source);
    }

    private function _returnSignupSuccess($source)
    {
        $request = Craft::$app->getRequest();
        if ($request->isAjax) {
            return json_encode(['status' => 'success']);
        }

        if(!is_null($source)){
            Craft::$app->getSession()->setFlash('lb-status-'.$source->shortcode, 'success');
            $redirect = $source->getConfigValue('redirect');
            if(!empty($redirect)){
                return $this->redirect(Yii::getAlias($redirect));
            }
        }

        return null;
    }

    private function _returnSignupError($source, $msg)
    {
        $request = Craft::$app->getRequest();
        if ($request->isAjax) {
            return json_encode([
                'status' => 'error',
                'message' => $msg
            ]);
        }
        if(!is_null($source)){
            Craft::$app->getSession()->setFlash('lb-status-'.$source->shortcode, 'error');
            Craft::$app->getSession()->setFlash('lb-status-error-'.$source->shortcode, $msg);
        }
        return null;
    }

    public function actionExport()
    {
        $batchSize = 2;
        $offset = 0;
        $output = '';
        $delimiter = ',';

        $headings = [
            'email',
            'date subscribed',
            'consent'
        ];

        $tempPath = Assets::tempFilePath();
        $f = fopen($tempPath, 'w');

        fputcsv($f, $headings, $delimiter);

        $signups = ListBuilder::$plugin->signupManager->getSomeSignups($batchSize, $offset);
        while(sizeof($signups) > 0){

            foreach($signups as $signup){
                $consent = $signup->consent == Enums::CONSENT__YES ? 'Yes' :
                    ($signup->consent == Enums::CONSENT__NO ? 'No' : 'Unknown');
                $line = [$signup->email, $signup->dateCreated->format('Y-m-d H:i:s'), $consent];
                fputcsv($f, $line, $delimiter);
            }

            $offset += $batchSize;
            $signups = ListBuilder::$plugin->signupManager->getSomeSignups($batchSize, $offset);
        }
        fclose($f);
        $date = (new \DateTime())->format('YmdHis');
        $filename = "subscriptions-".$date.'.csv';
        return Yii::$app->response->sendFile($tempPath, $filename);
    }

    private function fTime($time, $gran=-1) {

        $d[0] = array(1,"second");
        $d[1] = array(60,"minute");
        $d[2] = array(3600,"hour");
        $d[3] = array(86400,"day");
        $d[4] = array(604800,"week");
        $d[5] = array(2592000,"month");
        $d[6] = array(31104000,"year");

        $w = array();

        $return = "";
        $now = time();
        $diff = ($now-$time);
        $secondsLeft = $diff;
        $stopat = 0;
        for($i=6;$i>$gran;$i--)
        {
             $w[$i] = intval($secondsLeft/$d[$i][0]);
             $secondsLeft -= ($w[$i]*$d[$i][0]);
             if($w[$i]!=0)
             {
                $return.= abs($w[$i]) . " " . $d[$i][1] . (($w[$i]>1)?'s':'') ." ";
                 switch ($i) {
                    case 6: // shows years and months
                        if ($stopat==0) { $stopat=5; }
                        break;
                    case 5: // shows months and weeks
                        if ($stopat==0) { $stopat=4; }
                        break;
                    case 4: // shows weeks and days
                        if ($stopat==0) { $stopat=3; }
                        break;
                    case 3: // shows days and hours
                        if ($stopat==0) { $stopat=2; }
                        break;
                    case 2: // shows hours and minutes
                        if ($stopat==0) { $stopat=1; }
                        break;
                    case 1: // shows minutes and seconds if granularity is not set higher
                        break;
                 }
                 if ($i===$stopat) { break; }
             }
        }

        $return .= ($diff>0)?"ago":"left";
        return $return;
    }

}
