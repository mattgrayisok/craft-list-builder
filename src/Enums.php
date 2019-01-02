<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder;

abstract class Enums
{

    const SOURCE_TYPE__CUSTOM = 1;
    const SOURCE_TYPE__INLINE = 2;
    const SOURCE_TYPE__POPUP = 3;
    const SOURCE_TYPE__BAR = 4;

    const CONSENT__NO = 0;
    const CONSENT__YES = 1;
    const CONSENT__UNKNOWN = 2;

    const DESTINATION_TYPE__WEBHOOK = 1;
    const DESTINATION_TYPE__MAILCHIMP = 2;
    const DESTINATION_TYPE__SENDGRID = 3;
    const DESTINATION_TYPE__EMAIL_OCTOPUS = 4;
    const DESTINATION_TYPE__DRIP = 5;

    public static function sourceTypeToString($type)
    {
        $typeNames = [
            self::SOURCE_TYPE__CUSTOM => 'Manual',
            self::SOURCE_TYPE__INLINE => 'Inline',
            self::SOURCE_TYPE__POPUP => 'Popup',
            self::SOURCE_TYPE__BAR => 'Notification Bar',
        ];

        if(array_key_exists($type, $typeNames)){
            return $typeNames[$type];
        }
        return 'Unknown';
    }

    public static function destinationTypeToString($type)
    {
        $typeNames = [
            self::DESTINATION_TYPE__WEBHOOK => 'Webhook',
            self::DESTINATION_TYPE__MAILCHIMP => 'MailChimp',
            self::DESTINATION_TYPE__SENDGRID => 'SendGrid',
            self::DESTINATION_TYPE__EMAIL_OCTOPUS => 'Email Octopus',
            self::DESTINATION_TYPE__DRIP => 'Drip',
        ];

        if(array_key_exists($type, $typeNames)){
            return $typeNames[$type];
        }
        return 'Unknown';
    }

}
