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
class DripWrapper implements DestinationWrapperInterface
{
    public function syncSignups($signups, $destination = null)
    {

        if(is_null($destination)){
            return;
        }

        $config = json_decode($destination->config, true);
        $listId = $config['listid'];
        $apiKey = $config['apikey'];

        $attempts = [];

        /*$sendGrid = new \SendGrid($apiKey);

        $allRecipients = array_map(function($signup){
            return [
                'email' => $signup->email
            ];
        }, $signups);

        $response = $sendGrid->client->contactdb()->recipients()->post($allRecipients);

        $decoded = json_decode($response->body());
        if(!is_null($decoded)){
            if(isset($decoded->persisted_recipients)){
                $successIds = $decoded->persisted_recipients;
                $errorIndices = $decoded->error_indices;
            }else{
                //Got a response but it doesn't contain persisted recipients - must have had an error
                if(isset($decoded->errors) && sizeof($decoded->errors) > 0){
                    $msg = $decoded->errors[0]['message'];
                    if($msg == 'access forbidden') {
                        throw new DestinationException("API Key is invalid");
                    }
                    throw new DestinationException($msg);
                }
                throw new DestinationException("An unknown error occured");
            }
        }else{
            //Couldn't decode response - crap out
            throw new DestinationException("Error communicating with SendGrid");
        }

        if(empty($listId)){
            foreach($signups as $signup){
                $attempts[] = ['signupId' => $signup->id, 'success' => true];
            }
            return $attempts;
        }

        //Add all of these users to the specified list
        $response = $sendGrid->client->contactdb()->lists()->_($listId)->recipients()->post($successIds);
        $status = $response->statusCode();

        //Success from this call is a 201 with empty body
        if($status == 201){
            foreach($signups as $signup){
                $attempts[] = ['signupId' => $signup->id, 'success' => true];
            }
            return $attempts;
        }

        $decoded = json_decode($response->body());
        if(!is_null($decoded)){
            //Got a response but it doesn't contain persisted recipients - must have had an error
            if(isset($decoded->errors) && sizeof($decoded->errors) > 0){
                $msg = $decoded->errors[0]['message'];
                if($msg == 'List ID is invalid') {
                    throw new DestinationException("List ID is invalid");
                }
                throw new DestinationException($msg);
            }
            throw new DestinationException("An unknown error occured");
        }else{
            //Couldn't decode response - crap out
            throw new DestinationException("Error communicating with SendGrid");
        }*/

        return $attempts;

    }
}
