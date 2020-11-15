<?php

final class PhabricatorLocalTimeTestCase extends PhabricatorTestCase {

  protected function getPhabricatorTestCaseConfiguration() {
    return array(
      self::PHABRICATOR_TESTCONFIG_BUILD_STORAGE_FIXTURES => true,
    );
  }

  public function testLocalTimeFormatting() {
    $user = $this->generateNewTestUser();
    $user->overrideTimezoneIdentifier('America/Los_Angeles');

    $utc = $this->generateNewTestUser();
    $utc->overrideTimezoneIdentifier('UTC');

    $this->assertEqual(
      'Jan 1 2000, 12:00 AM',
      phabricator_datetime(946684800, $utc),
      pht('Datetime formatting'));
    $this->assertEqual(
      'Jan 1 2000',
      phabricator_date(946684800, $utc),
      pht('Date formatting'));
    $this->assertEqual(
      '12:00 AM',
      phabricator_time(946684800, $utc),
      pht('Time formatting'));

    $this->assertEqual(
      'Dec 31 1999, 4:00 PM',
      phabricator_datetime(946684800, $user),
      pht('Localization'));

    $this->assertEqual(
      '',
      phabricator_datetime(0, $user),
      pht('Missing epoch should fail gracefully'));
  }

}
