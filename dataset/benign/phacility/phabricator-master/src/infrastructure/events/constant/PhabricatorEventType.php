<?php

/**
 * For detailed explanations of these events, see
 * @{article:Events User Guide: Installing Event Listeners}.
 */
final class PhabricatorEventType extends PhutilEventType {

  const TYPE_DIFFERENTIAL_WILLMARKGENERATED = 'differential.willMarkGenerated';

  const TYPE_DIFFUSION_DIDDISCOVERCOMMIT    = 'diffusion.didDiscoverCommit';

  const TYPE_TEST_DIDRUNTEST                = 'test.didRunTest';

  const TYPE_UI_DIDRENDERACTIONS            = 'ui.didRenderActions';

  const TYPE_UI_WILLRENDEROBJECTS           = 'ui.willRenderObjects';
  const TYPE_UI_DDIDRENDEROBJECT            = 'ui.didRenderObject';
  const TYPE_UI_DIDRENDEROBJECTS            = 'ui.didRenderObjects';
  const TYPE_UI_WILLRENDERPROPERTIES        = 'ui.willRenderProperties';

  const TYPE_PEOPLE_DIDRENDERMENU           = 'people.didRenderMenu';
  const TYPE_AUTH_WILLREGISTERUSER          = 'auth.willRegisterUser';
  const TYPE_AUTH_DIDVERIFYEMAIL            = 'auth.didVerifyEmail';

}
