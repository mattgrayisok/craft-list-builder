<?php
/**
 * List Builder plugin for Craft CMS 3.x.
 *
 * Email mailing list popup notification exit
 *
 * @see      https://mattgrayisok.com
 *
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\destinationwrappers;

use mattgrayisok\listbuilder\exceptions\DestinationException;

/**
 * @author    Matt Gray
 *
 * @since     1.0.0
 */
class SendgridWrapper implements DestinationWrapperInterface
{
    public function syncSignups($signups, $destination = null)
    {
        if (is_null($destination)) {
            return;
        }

        $config = json_decode($destination->config, true);
        $listId = $config['listid'];
        $apiKey = $config['apikey'];

        $attempts = [];

        if (0 == sizeof($signups)) {
            return $attempts;
        }

        $sendGrid = new \SendGrid($apiKey);

        $allRecipients = array_map(function ($signup) {
            return [
                'email' => $signup->email,
            ];
        }, $signups);

        $fullData = ['contacts' => $allRecipients];
        if (!empty($listId)) {
            $fullData['list_ids'] = [$listId];
        }

        $response = $sendGrid->client->marketing()->contacts()->put($fullData);

        $decoded = json_decode($response->body());
        if (!is_null($decoded)) {
            if (!isset($decoded->job_id)) {
                //Got a response but it doesn't contain persisted recipients - must have had an error
                if (isset($decoded->errors) && sizeof($decoded->errors) > 0) {
                    $msg = $decoded->errors[0]->message;
                    if ('access forbidden' == $msg) {
                        throw new DestinationException('API Key is invalid');
                    }

                    throw new DestinationException($msg);
                }

                throw new DestinationException('An unknown error occured');
            }
        } else {
            //Couldn't decode response - crap out
            throw new DestinationException('Error communicating with SendGrid');
        }

        foreach ($signups as $signup) {
            $attempts[] = ['signupId' => $signup->id, 'success' => true];
        }

        return $attempts;
    }
}
