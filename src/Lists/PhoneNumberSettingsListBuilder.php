<?php

/**
 * @file
 * Contains \Drupal\sms\Lists\PhoneNumberSettingsListBuilder.
 */

namespace Drupal\sms\Lists;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\sms\Provider\PhoneNumberProviderInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Builds a list of phone number settings.
 */
class PhoneNumberSettingsListBuilder extends ConfigEntityListBuilder {

  /**
   * Storage for Phone Number Verification entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $phoneNumberVerificationStorage;

  /**
   * Phone number provider.
   *
   * @var \Drupal\sms\Provider\PhoneNumberProviderInterface
   */
  protected $phoneNumberProvider;

  /**
   * Current request time.
   *
   * @var int
   */
  protected $requestTime;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage('sms_phone_number_verification'),
      $container->get('request_stack'),
      $container->get('sms.phone_number')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $phone_number_verification_storage
   *   Storage for Phone Number Verification entities.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\sms\Provider\PhoneNumberProviderInterface $phone_number_provider
   *   The phone number provider.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, EntityStorageInterface $phone_number_verification_storage, RequestStack $request_stack, PhoneNumberProviderInterface $phone_number_provider) {
    parent::__construct($entity_type, $storage);
    $this->phoneNumberVerificationStorage = $phone_number_verification_storage;
    $this->phoneNumberProvider = $phone_number_provider;
    $this->requestTime = $request_stack
      ->getCurrentRequest()
      ->server
      ->get('REQUEST_TIME');
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['entity_type'] = $this->t('Entity type');
    $header['bundle'] = $this->t('Bundle');
    $header['count_expired'] = $this->t('Expired');
    $header['count_verified'] = $this->t('Verified');
    $header['count_unverified'] = $this->t('Unverified');
    $header['count_total'] = $this->t('Total');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\sms\Entity\PhoneNumberSettingsInterface $entity */
    $entity_type_id = $entity->getPhoneNumberEntityTypeId();
    $bundle = $entity->getPhoneNumberBundle();
    $row['entity_type'] = $entity_type_id;
    $row['bundle'] = $bundle;

    $config = $this->phoneNumberProvider->getPhoneNumberSettings($entity_type_id, $bundle);
    $lifetime = $config->get('verification_code_lifetime') ?: 0;

    $row['count_expired'] = $this->buildPhoneNumberVerificationQuery($entity_type_id, $bundle)
      ->condition('status', 0)
      ->condition('created', ($this->requestTime - $lifetime), '<')
      ->count()
      ->execute();

    $row['count_verified'] = $this->buildPhoneNumberVerificationQuery($entity_type_id, $bundle)
      ->condition('status', 1)
      ->count()
      ->execute();

    $row['count_unverified'] = $this->buildPhoneNumberVerificationQuery($entity_type_id, $bundle)
      ->condition('status', 0)
      ->count()
      ->execute();

    $row['count_total'] = $this->buildPhoneNumberVerificationQuery($entity_type_id, $bundle)
      ->count()
      ->execute();

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $render = parent::render();
    $render['table']['#empty'] = t('No phone number settings found.');
    return $render;
  }

  protected function buildPhoneNumberVerificationQuery($entity_type_id, $bundle) {
    return $this->phoneNumberVerificationStorage
      ->getQuery()
      ->condition('entity__target_type', $entity_type_id)
      ->condition('bundle', $bundle);
  }

}