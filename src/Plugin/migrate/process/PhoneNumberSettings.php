<?php

namespace Drupal\sms\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Updates SMS verification code message to D8 format.
 *
 * @MigrateProcessPlugin(
 *   id = "phone_number_settings"
 * )
 */
class PhoneNumberSettings extends ProcessPluginBase {

  const DEFAULT_LEGACY_VERIFICATION_MESSAGE = '[site:name] confirmation code: ';
//  const DEFAULT_LEGACY_VERIFICATION_MESSAGE = '[site-name] confirmation code: [confirm-code]';
  const DEFAULT_VERIFICATION_MESSAGE = "Your verification code is '[sms-message:verification-code]'.\nGo to [sms:verification-url] to verify your phone number.\n- [site:name]";

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($row->getSourceProperty('id') === 'sms_user_confirmation_message') {
      // Convert D6 message tokens to D7 token format.
      $value = str_replace('confirm-code', 'confirm:code', str_replace('site-name', 'site:name', $value));

      // If still using the D6/D7 default message, swap for the new D8 default.
      if (empty($value) || $value == static::DEFAULT_LEGACY_VERIFICATION_MESSAGE) {
        $value = static::DEFAULT_VERIFICATION_MESSAGE;
      }
      else {
        $value = str_replace('[confirm:code]', '[sms-message:verification-code]', $value);
      }
    }
    return $value;
  }

}
