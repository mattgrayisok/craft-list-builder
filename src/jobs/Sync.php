<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\jobs;

use mattgrayisok\listbuilder\ListBuilder;
use mattgrayisok\listbuilder\Enums;

use mattgrayisok\listbuilder\destinationwrappers\StubWrapper;
use mattgrayisok\listbuilder\destinationwrappers\WebhookWrapper;
use mattgrayisok\listbuilder\destinationwrappers\EmailOctopusWrapper;
use mattgrayisok\listbuilder\destinationwrappers\MailchimpWrapper;
use mattgrayisok\listbuilder\destinationwrappers\SendgridWrapper;
use mattgrayisok\listbuilder\destinationwrappers\DripWrapper;

use mattgrayisok\listbuilder\exceptions\DestinationException;
use mattgrayisok\listbuilder\exceptions\SignupSubmitException;

use Craft;
use Yii;
use craft\queue\BaseJob;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class Sync extends BaseJob
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $someAttribute = 'Some Default';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {

        $destinations = ListBuilder::$plugin->destinationManager->getEnabledDestinations();

        foreach($destinations as $destination){
            try{
                $pending = ListBuilder::$plugin->signupManager->getPendingSignupsForDestination($destination, 50);
                $wrapper = $this->destinationToWrapper($destination);
                $results = $wrapper->syncSignups($pending, $destination);

                $attemptsWithDestination = array_map(function($el) use ($destination) {
                    return [$el['signupId'], $destination->id, $el['success']];
                }, $results);

                Yii::$app->db->createCommand()
                    ->batchInsert('{{%listbuilder_signup_destination}}',
                    ['signupId', 'destinationId', 'success'],
                    $attemptsWithDestination
                    )
                    ->execute();

            } catch(DestinationException $e) {
                //There was a significant error with the destination
                //Set the destination as errored
                $destination->errorMsg = $e->getMessage();
                $destination->enabled = false;
                ListBuilder::$plugin->destinationManager->saveDestination($destination);
            } catch(\Exception $e) {
                //Some other exception occured
                //TODO
                $destination->errorMsg = $e->getMessage();
                $destination->enabled = false;
                ListBuilder::$plugin->destinationManager->saveDestination($destination);
            }
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('list-builder', 'Syncing subscriptions');
    }

    protected function destinationToWrapper($destination)
    {
        switch ($destination->type) {
            case Enums::DESTINATION_TYPE__WEBHOOK:
                return new WebhookWrapper();
                break;

            case Enums::DESTINATION_TYPE__EMAIL_OCTOPUS:
                return new EmailOctopusWrapper();
                break;

            case Enums::DESTINATION_TYPE__MAILCHIMP:
                return new MailchimpWrapper();
                break;

            case Enums::DESTINATION_TYPE__SENDGRID:
                return new SendgridWrapper();
                break;

            case Enums::DESTINATION_TYPE__DRIP:
                return new DripWrapper();
                break;

            default:
                throw new \Exception('No service wrapper for this destination');
                break;
        }
    }
}
