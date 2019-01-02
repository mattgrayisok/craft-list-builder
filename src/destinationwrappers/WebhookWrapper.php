<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\destinationwrappers;

use mattgrayisok\listbuilder\ListBuilder;
use mattgrayisok\listbuilder\exceptions\DestinationException;
use mattgrayisok\listbuilder\exceptions\SignupSubmitException;

use Craft;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class WebhookWrapper implements DestinationWrapperInterface
{
    public function syncSignups($signups, $destination = null)
    {

        if(is_null($destination)){
            return;
        }

        $config = json_decode($destination->config, true);

        $attempts = [];

        foreach($signups as $signup){
            Craft::info(
                "Syncing email ".$signup->email." via webhook",
                __METHOD__
            );

            $postVals = [
                'email' => $signup->email
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $config['url']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postVals));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close ($ch);

            //The request is only considered a success if we get a 200 back
            if ($httpcode == 200) {
                //Record that we made the attempt and it was a success
                $attempts[] = ['signupId' => $signup->id, 'success' => true];
                continue;
            }

            //Handle unexpected errors
            if(!isset($config['disableonerror']) || $config['disableonerror']){
                throw new DestinationException('A remote server error occured');
            }else{
                //If we're ignoring errors just set this as a failure so it isn't retried
                $attempts[] = ['signupId' => $signup->id, 'success' => false];
            }


        }

        return $attempts;

    }
}
