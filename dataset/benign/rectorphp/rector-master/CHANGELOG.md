
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

PRs and issues are linked, so you can find more about it. Thanks to [ChangelogLinker](https://github.com/Symplify/ChangelogLinker).

<!-- changelog-linker -->

## Unreleased

### Added

- [#3571] [Autodiscovery] add types/suffix support to MoveValueObjectsToValueObjectDirectoryRector
- [#3572] Add rename class support in twig/latte as well
- [#3566] added support post class move rename in XML files, Thanks to [@vladyslavstartsev]
- [#3565] Add post class move rename in neon/yaml files
- [#3577] add NonEmptyArrayTypeMapper

### Changed

- [#3578] return type - skip void
- [#3574] skip common patterns in value object
- [#3573] allow skip null scope

### Fixed

- [#3576] fix return type in abstract class

## [v0.7.37] - 2020-06-21

### Changed

- [#3559] "$this->"" needs to become "static::" instead of "self::", Thanks to [@derrickschoen]
- [#3562] Correct spelling mistake, Thanks to [@PurpleBooth]
- [#3561] decopule AbstractFileMovingFileSystemRector

### Fixed

- [#3560] Fix double move of classes
- [#3563] Fix node printing with post-race condition

## [v0.7.36] - 2020-06-19

### Added

- [#3558] Add parsed tokens and stmts object

### Fixed

- [#3557] fix importing of soon-to-be-existing classes

## [v0.7.35] - 2020-06-19

### Added

- [#3550] [Autodiscovery] add class-rename to MoveServicesBySuffixToDirectoryRector
- [#3535] [Experimental] Add safe_types option
- [#3539] Re-add --only option
- [#3553] add TokensByFilePathStorage + decouple file system rules to own step
- [#3538] [PHP 7.2] Add ReplaceEachAssignmentWithKeyCurrentRector
- [#3548] Add [@fixme], if ini_set/init_get removal breaks the code

### Changed

- [#3536] [Autodiscovery] rename interface used in other places too
- [#3549] [Autodiscovery] Skip control factory in interface split
- [#3552] [PHP] Handle arrow functions in AddDefaultValueForUndefinedVariableRector, Thanks to [@fsok]
- [#3551] skip Form factories too
- [#3546] allow new phpstan
- [#3547] Expand CakePHP 4.1 standard, Thanks to [@markstory]
- [#3555] implements rename

### Fixed

- [#3556] Fix imported interface renaming

### Removed

- [#3554] remove MoveAndRenameNamespaceRector, not tested and not working, prefer class rename

## [v0.7.34] - 2020-06-16

### Added

- [#3507] [CodeQuality] Add ArrayThisCallToThisMethodCallRector
- [#3528] [Order] Add OrderClassConstantsByIntegerValueRector
- [#3519] [Rector] Add UpdateFileNameByClassNameFileSystemRector
- [#3527] Add PHPUnit 9.0 regex method name changes
- [#3523] add NullableTypeMapper
- [#3511] Add rector rules for deprecated features in CakePHP 4.1, Thanks to [@markstory]
- [#3534] [docs] make code sample required for Rector rules, add code highlight

### Changed

- [#3506] [Autodiscovery] Do not nest already correct name
- [#3487] [CakePHP] Convert array options to fluent method calls, Thanks to [@garas]
- [#3503] I found a bug with the ChangeNestedForeachIfsToEarlyContinueRector, Thanks to [@derrickschoen]
- [#3512] Don't type hint to traits, Thanks to [@UFTimmy]
- [#3524] skip encapsed string from constant extraction
- [#3520] skip multi assign ChangeReadOnlyVariableWithDefaultValueToConstantRector
- [#3525] [PHP 7.4] Make array spread work only for integer keys
- [#3533] decouple more responsibility to TagValueNodeConfiguration

### Fixed

- [#3526] Fix for array of callable print in phpdoc
- [#3532] Fix quote in array values of phpdoc tags, decouple TagValueNodeConfiguration
- [#3508] Fix regex to account for windows., Thanks to [@UFTimmy]
- [#3522] fix [@template] tag preposition
- [#3521] fix nested comment in nested foreach to if

### Removed

- [#3518] Simplify ExplicitPhpErrorApiRector and remove replaceNode() use

## [v0.7.32] - 2020-06-10

### Added

- [#3505] add parent::__construct() in case of existing empty ctor

## [v0.7.31] - 2020-06-09

- [#3502] [Architecture] Add typed property support for [@inject] to ctor rule

## [v0.7.30] - 2020-06-09

- [#3479] [NetteUtilsCodeQuality] Add ReplaceTimeNumberWithDateTimeConstantRector
- [#3491] [Restoration] Add RemoveUselessJustForSakeInterfaceRector
- [#3483] [Restoration] Add MakeTypedPropertyNullableIfCheckedRector
- [#3475] [Sensio] Add [@route] migration to Symfony
- [#3476] [Sensio] Add RemoveServiceFromSensioRouteRector
- [#3477] [PHP 7.4] Add conflicting short import typed property fix
- [#3492] Add support for rename of [@property]
- [#3498] Add return type replacement in TemplateAnnotationRector

### Changed

- [#3486] test children inject typed
- [#3484] [PHP 7.4] Allow run typed properties on class-like types only"
- [#3482] [PHP 7.4] Prevent already used property name
- [#3481] [PHP 7.4] Prevent child typed property override by abstract
- [#3497] TemplateAnnotationRector improvements
- [#3499] Update documentation link in PhpRectorInterface, Thanks to [@RusiPapazov]

### Fixed

- [#3488] PHPStan compatibility fixes, Thanks to [@ondrejmirtes]
- [#3494] Fix typo in rector_rules_overview.md, Thanks to [@berezuev]
- [#3485] fix skip non-class for nullable types
- [#3495] fix method call name
- [#3496] constant table fix
- [#3478] fix prefixed GetToConstructorInjectionRector classes
- [#3474] Various [@template]/@method annotation fixes

### Removed

- [#3460] Update docker commands to remove container on exit, Thanks to [@codereviewvideos]

## [v0.7.29]

### Added

- [#3456] [CodingStyle] Add RemoveDoubleUnderscoreInMethodNameRector
- [#3470] add fix for preslah of entity class
- [#3455] Add RemoveFuncCallArgRector, ClearReturnNewByReferenceRector, RemoveIniGetSetFuncCallRector, ReplaceHttpServerVarsByServerRector
- [#3459] [PHP 8.0] Add RemoveUnusedVariableInCatchRector
- [#3458] [PHP 8.0] Add TokenGetAllToObjectRector

### Changed

- [#3450] update to phpstan/phpstan-phpunit 0.12.9
- [#3457] [PHP 7.2] Various improvements in ListEach and WhileEach Rectors
- [#3453] Future-proof ScopeFactory, Thanks to [@ondrejmirtes]
- [#3468] use string for classes in doc node factory
- [#3472] [phar] Un-pre-slash strings that should be clean
- [#3467] [phar] unprefix class strings

### Fixed

- [#3466] [Symfony 2.8] Fix ArgumentDefaultValueReplacerRector to work with bool values
- [#3469] fix orm prefix
- [#3451] Fixed link to nodes overview in own rector code sample, Thanks to [@norberttech]
- [#3471] phar extra slashes fix
- [#3452] [testing] Rework AbstractRunnableRectorTestCase to be part of main test + fix ListEachRector behavior

## [v0.7.27] - 2020-05-30

### Added

- [#3399] [Nette] Add ContextGetByTypeToConstructorInjectionRector
- [#3434] [NetteKdyby] Add ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector
- [#3416] [NetteKdyby] Add ReplaceMagicPropertyEventWithEventClassRector
- [#3429] [Privatization] Add PrivatizeFinalClassMethodRector
- [#3411] [Privatization] Add property privatization rule
- [#3446] [Restoration] Add CompleteMissingDependencyInNewRector
- [#3396] [SOLID] add InjectMethodFactory for multi parent abstract rector
- [#3392] [SOLID] Add MultiParentingToAbstractDependencyRector
- [#3410] Add Drupal logo + link to Drupal Rector rules to README.md, Thanks to [@shaal]
- [#3398] add ParsedClassConstFetchNodeCollector
- [#3418] add addComment() to Rector, fix comment preserving
- [#3350] Add [@mixin] support from PHPStan
- [#3422] Add Report and Extension package
- [#3419] Configuration - add getOutputFormat()
- [#3421] adding fixture for validation with message, Thanks to [@bitgandtter]
- [#3417] compensate comments added on too nested node
- [#3423] Add AfterProcessEvent and AfterReportEvent
- [#3441] [Nette Kdyby] Add direct event class support for string-only based events
- [#3437] [Nette Kdyby] Add support for unique dim fetch event param name
- [#3439] [Nette Kdyby] Add under_score dim fetch support, prevent double event fill override
- [#3440] [Nette Kdyby] Add ReplaceEventManagerWithEventSubscriberRector
- [#3442] [Nette Kdyby] do not add getter for unused param in listener method
- [#3387] [compiler] Add ScoperTest

### Changed

- [#3415] [CodingStyle] UnderscoreToCamelCaseVariableAndPropertyNameRector
- [#3449] [Nette Kdyby] refactor to EventAndListenerTree
- [#3447] [Nette Kdyby] Sync getters in listener method and event class
- [#3377] composer: bump to Symplify 8-dev
- [#3445] [Nette Kdyby] Skip control events"
- [#3393] Guard against ShouldNotHappenException, Thanks to [@UFTimmy]
- [#3395] Keep empty php code as is, Thanks to [@shaal]
- [#3390] Use Newline from Standard Printer, Thanks to [@tavy315]
- [#3412] skip public properties
- [#3406] [Kdyby to Contributte] Migrate events
- [#3435] improve CustomEventFactory
- [#3428] Keep new line untouched

### Fixed

- [#3379] fix missing doc for property type infering
- [#3443] [Nette Kdyby] Fix event undescore_variable name
- [#3403] Fix typo in StrStartsWithRector code sample, Thanks to [@guilliamxavier]
- [#3407] Fix SimplifyArraySearchRector w.r.t. "strictness", Thanks to [@guilliamxavier]
- [#3427] fix-readding comment
- [#3386] Fix RenameAnnotationRector for null phpdoc, Thanks to [@eclipxe13]
- [#3378] fix doc FQN importing
- [#3345] Fix invalid path, Thanks to [@ddziaduch]

### Removed

- [#3400] remove interface suffix/prexit for PropertyNaming

## [v0.7.26] - 2020-05-16

### Added

- [#3351] [CodingStyle] Add SplitGroupedUseImportsRector
- [#3358] [Legacy] Add FunctionToStaticMethodRector
- [#3369] [PSR-4] Add NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector
- [#3362] [PHP 5.5] Fix StringClassNameToClassConstantRector for importing freshly added class names
- [#3355] [docs] add counter next to Rector group

### Changed

- [#3372] [MockistaToMockery] init
- [#3348] Decoule ClassRenamer, improve NormalizeNamespaceByPSR4ComposerAutoloadRector
- [#3361] Rule Rector\ClassMethod\AddArrayReturnDocTypeRector could not process files, Thanks to [@MetalArend]
- [#3359] Rule "Rector\ClassConst\VarConstantCommentRector" keeps throwing "could not process" errors., Thanks to [@MetalArend]

### Deprecated

- [#3367] [ElasticsearchDSL] Deprecate single custom rule, better handled by community
- [#3366] [Oxid] Deprecate single custom rule, better handled by community
- [#3365] [Shopware] Deprecate single custom rule, better handled by community
- [#3363] [Silverstripe] deprecate, handled by community
- [#3364] [Sylius] Deprecate single custom rule, better handled by community
- [#3376] remove deprecated AutoReturnFactoryCompilerPass

### Fixed

- [#3356] [CodeQuality] Fix SimplifyIfReturnBoolRector for else if

## [v0.7.23] - 2020-05-11

### Added

- [#3325] [SOLID] Add RepeatedLiteralToClassConstantRector
- [#3346] Add symplify/parameter-name-guard
- [#3328] [utils] add FindFirstInstanceOfReturnTypeExtension to PHPStan extension

### Changed

- [#3320] Resolving todos [#5]
- [#3321] Resolving todos [#6]
- [#3322] node printing functions consolidation
- [#3326] Make use of createMethodCall()
- [#3332] Define `__RECTOR_RUNNING__` constant at analysis time, Thanks to [@staabm]
- [#3340] move MultipleClassFileToPsr4ClassesRector to PSR4
- [#3343] Alter command name, Thanks to [@ddziaduch]

### Deprecated

- [#3324] Deprecate Zend 1 to Symfony 4 set

### Fixed

- [#3344] Fix link and it's name, Thanks to [@ddziaduch]
- [#3338] Various fixes
- [#3341] fix namespace
- [#3327] various PHPStan fixes

## [v0.7.22] - 2020-05-05

### Added

- [#3293] [Naming] Add RenamePropertyToMatchTypeRector
- [#3295] [Naming] Fix duplicate name on already existing + add "naming" set to CI
- [#3305] [Order] Add OrderPropertyByComplexityRector
- [#3301] [Order] Add OrderPrivateMethodsByUseRector
- [#3304] [Order] Add OrderPublicInterfaceMethodRector
- [#3289] [Performance] Add PreslashSimpleFunctionRector
- [#3290] [SOLID] Add AddFalseDefaultToBoolPropertyRector
- [#3277] [PHP 8.0] Add ClassOnObjectRector
- [#3285] Add resolving for private properties of annotation object
- [#3283] simplify Param node factory + add generic phpdoc class-node factory
- [#3279] [PHP 8.0] Add get_debug_type()
- [#3278] [PHP 8.0] Add static type
- [#3308] add = for [@Route] options separator
- [#3254] [PHP 8.0] Add attributes v2

### Changed

- [#3294] [Naming] Decouple ConflictingNameResolver and ExpectedNameResolver
- [#3273] use propety over property property
- [#3270] TagValueNode refactoring
- [#3269] [#3268] - rector should scan linked directories, Thanks to [@atompulse]
- [#3280] refactoring to generic items
- [#3281] moving to generic tag node
- [#3284] merge more factories to MultiPhpDocNodeFactory
- [#3288] Improve $node name get on static or method call
- [#3241] Symfony Route annotation needs equal sign, not colon, Thanks to [@stephanvierkant]
- [#3310] workflow: generate changelog
- [#3319] Resolving todos [#4]
- [#3309] workflow: generate documentation
- [#3306] do not export compiler as part of package
- [#3313] Resolving todos
- [#3314] Resolving todos [#2]
- [#3300] various coding standard improvements
- [#3299] Tests for [@noRector], Thanks to [@tomasnorre]
- [#3298] Update Symplify deprecations
- [#3318] symplify NodeDumper to PHP code test
- [#3297] [Utils] PHPStan rule improvements

### Fixed

- [#3317] Fix when PropertyProperty is a subnode of Property, Thanks to [@tomasnorre]
- [#3275] Fix constant referencing in annotations
- [#3311] Fix comment removing

### Removed

- [#3302] [CakePHPtoSymfony] Remove unfinished set
- [#3287] [DX] drop confusing --only option to promote config
- [#3296] remove scan-fatal-errors, move to migrify

<!-- dumped content end -->

## Unreleased

### Changed

- [#3266] publish dump-rectors command with 3rd party install

## [v0.7.20] - 2020-04-26

- [#3253] make Doctrine property inferer skip non doc
- [#3262] [cs] sort private methods by call order and property by complexity
- [#3265] [docs] make dump-rectors command open to public

### Fixed

- [#3252] [CodeQuality] Fix CompactToVariablesRector for unknown names
- [#3260] [Nette] Fix preg_match_all() to Nette\Utils migrations
- [#3261] Fix autoloading for phpstan configs
- [#3248] Fix [@Route] name can be empty, Thanks to [@stephanvierkant]

### Removed

- [#3264] remove RectorStandaloneRunner, too hacky

## [v0.7.19] - 2020-04-24

### Added

- [#3251] [CodeQuality] Add UnusedForeachValueToArrayKeysRector
- [#3235] [DX] Add validate fixture suffix
- [#3237] [PHP 8.0] Add str_ends_with()
- [#3245] [PHP 8.0] Add Stringable
- [#3228] [PHP 8.0] Add str_starts_with - rule [#500] 🎉🎉🎉

### Changed

- [#3242] Update rector counter, Thanks to [@vladyslavstartsev]
- [#3238] StrStartWith refactoring
- [#3239] simplify throws class resolving
- [#3240] Symfony route with prefix, Thanks to [@stephanvierkant]
- [#3244] docs refactoring

### Fixed

- [#3236] various suffix fixes
- [#3233] Fix single-line doc end

## [v0.7.18] - 2020-04-23

### Changed

- [#3224] https://github.com/rectorphp/rector/issues/3223, Thanks to [@atompulse]

### Fixed

- [#3232] Compiler: Fix invalid changes in config/set neon files, Thanks to [@RiKap]

## [v0.7.17] - 2020-04-23

### Added

- [#3219] [Restoration] Add RemoveFinalFromEntityRector
- [#3218] Add more tag value node tests
- [#3196] [CodeQuality] Add SplitListScalarAssignToSeparateLineRector
- [#3195] [PHPUnit] Add strict param to ReplaceAssertArraySubsetRector
- [#3118] Add cache for un-changed files
- [#3193] add condition test for [@Route]

### Changed

- [#3175] [CodeQuality] Make ChangeArrayPushToArrayAssignRector skip spread
- [#3211] [PhpDoc] Use generic approach to TagValueNode annotations quotes and explicitness
- [#3182] [Privatization] Make PrivatizeLocalOnlyMethodRector skip entities
- [#3156] Upgrade to php-parser 4.4
- [#3174] include PHPExcel_Worksheet_PageSetup
- [#3186] Updating the link to Drupal-Rector repo, Thanks to [@shaal]
- [#3187] Test with Drupal-Rector project to discover BC breaks early on, Thanks to [@shaal]
- [#3217] reorganize phpdoc reprint test
- [#3194] Spacing + quoting improvements in [@Route]
- [#3197] various improvements
- [#3198] use FileInfo in Parser
- [#3200] Run rector list in CI to detect potential issues, Thanks to [@JanMikes]
- [#3215] improve TagValueNodes

### Fixed

- [#3199] [PHPUnit] Fix ExceptionAnnotationRector for null phpdoc
- [#3191] [PHP 7.3] Fix regex slash escaping
- [#3177] fix back annotation
- [#3176] Type and Choice fixes
- [#3204] various fixes

### Removed

- [#3216] drop hide autoload errors

## [v0.7.16] - 2020-04-16

### Added

- [#3172] [PHPOffice] Add IncreaseColumnIndexRector

## [v0.7.15] - 2020-04-16

### Changed

- [#3168] [PHPOffice] Init migration to PHPSpreadSheets
- [#3171] Cleanup

## [v0.7.14] - 2020-04-14

### Added

- [#3164] [DeadCode] Add RemoveUnusedAssignVariableRector

### Fixed

- [#3161] fix comment removal

## [v0.7.12] - 2020-04-13

### Added

- [#3155] Add extra allowed interface to EntityAliasToClassConstantReferenceRector, Thanks to [@acrobat]
- [#3154] provide config + add post Rectors for FileSystemRector

### Fixed

- [#3153] [DeadCode] Fix RemoveUnusedDoctrineEntityMethodAndPropertyRector for id

## [v0.7.11] - 2020-04-08

- [#3152] fix ThisCallOnStaticMethodToStaticCallRector in prefixed rector

## [v0.7.10] - 2020-04-08

### Changed

- [#3151] check for array at UselessIfCondBeforeForeachDetector

## [v0.7.9] - 2020-04-08

### Fixed

- [#3149] fix sniff public
- [#3148] fix PrivatizeLocalOnlyMethodRector for event subscriber methods

## [v0.7.8] - 2020-04-07

### Added

- [#3108] [DeadCode] Add RemoveDeadRecursiveClassMethodRector
- [#3117] [PHPUnit] Add AddProphecyTraitRector
- [#3093] [Privatization] Add PrivatizeLocalGetterToPropertyRector
- [#3100] [Privatization] Add PrivatizeLocalPropertyToPrivatePropertyRector
- [#3116] move node adding to PostRector
- [#3140] add ClassSyncerNodeTraverser
- [#3089] Add RunnableTestCase to run fixed code in a test, Thanks to [@paslandau]
- [#3094] Add parallel execution to ci/run_all_sets, Thanks to [@paslandau]
- [#3134] Add DoctrineAnnotationParserSyncer to prevent doctrine/annotation constant by-value override
- [#3114] move property adding to PostRector
- [#3141] [PHPUnit 9.1] Add assertFileNotExists() method rename
- [#3080] [CodeQuality] Add ArrayKeysAndInArrayToIssetRector
- [#3070] [DeadCode] Add empty() + count($values) > 0 checks to RemoveUnusedNonEmptyArrayBeforeForeachRector
- [#3068] [DeadCode] Add RemoveAssignOfVoidReturnFunctionRector
- [#3062] [DeadCode] Add RemoveUnusedFunctionRector
- [#3066] [DeadCode] Add RemoveUnusedNonEmptyArrayBeforeForeachRector
- [#3047] [PHPUnit] Add CreateMockToCreateStubRector
- [#3081] [TypeDeclaration] Add class method param type resolving by property
- [#3058] [PHP 7.4] Add default null type on properties
- [#3059] [PHP 7.4] Add restoration null default only
- [#3057] [PHP 7.4] Add id tag support + remove array on collection property
- [#3078] Add Safe 0.7 set
- [#3072] [PHP 8.0] Add StrContainsRector

- [#3111] [API] NodeRemovingCommander to PostRector
- [#3084] [Privatization] Privatize methods that are used only locally
- [#3120] Improve performance
- [#3097] move ci validation scripts to objectivy ProjectValidator package
- [#3139] Correct sentence in README.md, Thanks to [@callmebob2016]
- [#3092] Cleanup
- [#3137] Fixup has same commit message, Thanks to [@JanMikes]
- [#3136] Copy .git directory into docker image, Thanks to [@JanMikes]
- [#3103] move constant privatization to privatization set
- [#3113] move name-importing to PostRector
- [#3106] Require symfony 5.0.6 or 4.4.6, Thanks to [@UFTimmy]
- [#3128] Allow PHPStan generics
- [#3115] move node-replacing to PostRector
- [#3146] Make sure doctrine alias rector works in symfony controllers, Thanks to [@acrobat]
- [#3082] [CodeQuality] use array_key_exists instead of isset
- [#3056] [PHP 7.4] Improve TypedPropertyRector for Doctrine collection
- [#3051] improve GeneratedValueTagValueNode
- [#3063] [PHP 5.5] Prevent error on non-string value in PregReplaceEModifierRector
- [#3040] Proofread docs, Thanks to [@greg0ire]
- [#3039] Proofread readme, Thanks to [@greg0ire]
- [#3083] use just one type of printing

### Fixed

- [#3050] Fix assert choice tag value node with class constant reference
- [#3049] fix union type on ReturnTypeDeclarationRector
- [#3052] fix content resolving
- [#3054] skip if not used with the `array []` operator fixes [#3053], Thanks to [@derflocki]
- [#3065] Fix multiple annotation reading of same type at class method
- [#3069] Fix Route separating key
- [#3077] Fix auto import
- [#3079] Fix annotation in requirements of [@Route]
- [#3064] [PHP 7.4] Fix ChangeReflectionTypeToStringToGetNameRector
- [#3132] Fix Gedmo annotation printing
- [#3130] Fix missing array key in ArrayMergeOfNonArraysToSimpleArrayRector
- [#3096] Fix [@Route] localized paths
- [#3129] fix missing host at [@Route] annotation

### Removed

- [#3122] remove json rector dump formatter, not needed
- [#3071] remove ctor dependency on property/assign removal
- [#3076] [PHP 8.0] drop preg_match support from StrContains, too vague

## [v0.7.7] - 2020-03-20

### Added

- [#3024] add DoctrineBehaviors 2.0
- [#3019] add fix for getIterator() on Finder for Array spread
- [#3034] Add checkstyle output format
- [#3021] add phpunit 9 rector to convert non-strict assertContains, Thanks to [@nightlinus]
- [#3023] add DoctrineBehaviors 2.0

### Changed

- [#3032] [DeadCode] Skip shifted variable
- [#3015] Rector CI is now exclusive for non-fork pushes + PRs, Thanks to [@JanMikes]
- [#3013] Commit rector processed changes from CI, Thanks to [@JanMikes]
- [#3036] Run cs after rector ci, Thanks to [@JanMikes]
- [#3027] ForToForeachRector fixture, Thanks to [@crishoj]

### Fixed

- [#3029] Fix other loop
- [#3022] PHP 7.4 deprecation fix, Thanks to [@alexeyshockov]
- [#3030] fix no-space change reprint in case of dual comment
- [#3035] Fix typo in README.md, Thanks to [@pgrimaud]
- [#3031] fix asterisk indent

### Removed

- [#3016] Delete DogFoodClass, Thanks to [@JanMikes]

## [v0.7.4] - 2020-03-11

### Added

- [#3003] Add failing tests for method annotation, Thanks to [@stedekay]
- [#2990] AssertTrueFalseToSpecificMethodRector: add broken test ('Pick more specific node than PhpParser\Node\Expr\StaticCall'), Thanks to [@gnutix]
- [#2988] add space between name and value

### Changed

- [#3009] [ReadyToBeMerged][AnnotateThrowables] Support `$this` calling a method of the same class, Thanks to [@Aerendir]
- [#3012] check for used variable without comments
- [#2981] Skip passed argument
- [#2984] improve array shape double collon spacing
- [#2987] improve param array type for change type
- [#2980] skip empty method on open-source
- [#3010] Abstract files system
- [#2998] AnnotateThrowables: support analysis of called functions and methods, Thanks to [@Aerendir]

### Fixed

- [#3008] [ReadyToBeMerged][AnnotateThrowables] Fix a small mispelling., Thanks to [@Aerendir]
- [#2992] fix spacing of data provider
- [#2997] Fix various static calls errors in PHPUnit Rectors., Thanks to [@gnutix]
- [#3004] fix method annotation
- [#2985] fix union param
- [#2982] Fix PhpDocInfoPrinter slash removal
- [#2996] fix multiline with one space

### Removed

- [#3005] remove comments only in case of change to original node

## [v0.7.3] - 2020-03-01

### Added

- [#2948] [DeadCode] Add RemoveDuplicatedIfReturnRector
- [#2950] [GetClassOnNullRector] Add failing test in trait., Thanks to [@gnutix]
- [#2953] AssertRegExpRectorTest: add broken test on static method call, Thanks to [@gnutix]
- [#2956] AssertTrueFalseInternalTypeToSpecificMethodRector: add broken test method call, Thanks to [@gnutix]
- [#2952] ReturnTypeDeclarationRector: add broken test on array indexes (?), Thanks to [@gnutix]
- [#2951] AddSeeTestAnnotationRectorTest: add broken test for simple comment., Thanks to [@gnutix]
- [#2943] add failing test case for [#2939], Thanks to [@fsok]
- [#2954] RemoveDefaultArgumentValueRector: add broken test on static method call., Thanks to [@gnutix]
- [#2968] Class Cognitive complexity improvements + add docContent as first step to format preserving of doc nodes

### Changed

- [#2974] [DX] Improve ForToForeachRector
- [#2966] make open-source parameter typo-proof
- [#2969] decopule class dependency manipulator methods
- [#2949] Improve PropertyFetchManipulator
- [#2972] Simplify PropertyFetchManipulator
- [#2962] cleanup extra space in doc print
- [#2975] Decrease class complexity <=50
- [#2947] Let amount of usages decide whether whitespaces or tabs are used, Thanks to [@alexanderschnitzler]

### Fixed

- [#2978] [DeadCode] Fix shifted value
- [#2979] fix spacing for array shape item
- [#2959] fix return dim array fetch
- [#2960] fix naming of non-func call
- [#2963] fix get class on trait
- [#2964] fix complexity
- [#2965] fix extra space in phpdoc
- [#2977] fix array shape type
- [#2961] various fixes

### Removed

- [#2946] remove dead code

## [v0.7.2] - 2020-02-27

### Added

- [#2924] [CodeIgniter] Add 4.0 set
- [#2941] Make compiler own kernel app + add more debug info
- [#2940] add Windows print test
- [#2933] add project_type
- [#2931] add has lifecycle callbacks
- [#2925] add docs space test

### Changed

- [#2926] [DeadCode] Skip abstract methods in RemoveUnusedParameterRector as 3rd contract

### Fixed

- [#2935] Fix double boolean
- [#2934] fix [@method] union return type annotation
- [#2932] Fix prophecy mocking arg

### Removed

- [#2937] skip open source class in remove unused param in open-source

## [v0.7.1] - 2020-02-23

### Added

- [#2906] [CodeQuality] Add InlineIfToExplicitIfRector
- [#2898] [CodingStyle] Add CamelCaseFunctionNamingToUnderscoreRector
- [#2919] [DeadCode] Add RemoveUnusedVariableAssignRector
- [#2918] [DeadCode] Add RemoveUnusedClassConstantRector
- [#2914] [JMS] Add RemoveJmsInjectParamsAnnotationRector and RemoveJmsServiceAnnotationRector
- [#2920] [MysqlToMysqli] Add MysqlQueryMysqlErrorWithLinkRector
- [#2917] [Phalcon] Add DecoupleSaveMethodCallWithArgumentToAssignRector
- [#2907] [SOLID] Add ChangeNestedForeachIfsToEarlyContinueRector
- [#2873] [SOLID] Add ChangeReadOnlyVariableWithDefaultValueToConstantRector
- [#2901] add links to each rule to docs
- [#2902] add mergeable
- [#2862] Adding failing test for RemoveAlwaysElseRector, Thanks to [@escopecz]
- [#2867] Add failing test for issue [#2863], Thanks to [@fsok]
- [#2853] [DeadCode] Add RemoveDeadTryCatchRector
- [#2856] [SOLID] Add ChangeReadOnlyPropertyWithDefaultValueToConstantRector
- [#2848] add first OXID rector, Thanks to [@alfredbez]

### Changed

- [#2883] [OXID] replace backwards-compatability classes in oxNew, Thanks to [@alfredbez]
- [#2886] skip test fixtures
- [#2872] Skip unpackaged args in ArraySpreadInsteadOfArrayMergeRector
- [#2871] Make ParamTypeDeclaration test pass with parent interface
- [#2874] Update set for transforming Kdyby\Translation to Contributte\Translation, Thanks to [@Ivorius]
- [#2869] Keep comments
- [#2868] update CHANGELOG
- [#2875] disable coverage on pr, secret does not work
- [#2876] `AnnotateThrowablesRector`: Improve organization of tests., Thanks to [@Aerendir]
- [#2881] Callable type falling tests, Thanks to [@snapshotpl]
- [#2884] Callable type
- [#2922] skip used property
- [#2890] Support throw of static methods, Thanks to [@Aerendir]
- [#2904] Support throw from the method of an instantiated class., Thanks to [@Aerendir]
- [#2916] improve complexity
- [#2915] improve EregToPcreTransformer complexity
- [#2913] replace SHORT_NAME with short name interface
- [#2909] Rector CI: enable SOLID set
- [#2905] Fixup
- [#2903] prevent getName() on StaticCall or MethodCall
- [#2896] keep array function static
- [#2631] [AddArrayReturnDocTypeRector] sets a less specific type in child method (mixed[]) than is defined in parent method (SomeObject[]), Thanks to [@gnutix]
- [#2650] [CountOnNullRector] Should understand array/countable variable in trait method, Thanks to [@gnutix]
- [#2860] Make `AnnotateThrowablesRector` continue on unhandled node types., Thanks to [@Aerendir]
- [#2859] Apply properties to constants rule from SOLID
- [#2858] Cleanup AnnotateThrowablesRector
- [#2857] Improve AnnotateThrowablesRector
- [#2851] move Nette package to rules

### Fixed

- [#2865] fix callable print [closes [#2841]]
- [#2866] Skip CountOnNullRector on trait + fix return type mixed override
- [#2885] Fix already constant
- [#2900] Fix incorrect regexes to preserve doc tags spacing
- [#2899] fix duplicate switch without break
- [#2880] Fix PHP notice in ternary to spaceship rector, Thanks to [@fsok]
- [#2870] fix remove alwasy else for anonymous function jump
- [#2921] Fix FinalizeClassesWithoutChildrenRector for embedable
- [#2893] fix tab indent
- [#2897] fix nested array dim fetch resolving type

## [v0.7.0] - 2020-02-14

### Added

- [#2795] [CakePHPToSymfony] Add CakePHPBeforeFilterToRequestEventSubscriberRector
- [#2850] [PHPStan] Add KeepRectorNamespaceForRectorRule
- [#2784] [PHPUnit] feature: add rule to refactor exception methods, Thanks to [@alfredbez]
- [#2849] [Renaming] Add RenameFuncCallToStaticCallRector
- [#2811] Add support for phpunit 9, Thanks to [@snapshotpl]
- [#2843] add more checks to 'composer complete-check', Thanks to [@alfredbez]
- [#2802] added --config parameter to README, Thanks to [@C0pyR1ght]

### Changed

- [#2781] [PhpDoc] move get param types to php doc info
- [#2830] Decouple Static Type Mapper
- [#2838] move Rector-rule based packages from /packages to /rules
- [#2756] github-action: Annotate Github Pull Requests based on a Checkstyle X…, Thanks to [@staabm]
- [#2829] decouple PropertyFetchTypeResolver
- [#2847] move core architecture to own set
- [#2775] Merge pull request [#2775] from rectorphp/php-doc-object-attribute
- [#2845] improve original format in CallableTypeNode
- [#2783] Merge pull request [#2783] from rectorphp/cleanup-parsed-nodes
- [#2844] Update to PHPStan 0.12.10 stable
- [#2786] Merge pull request [#2786] from rectorphp/php-doc-only
- [#2792] decouple ImplicitToExplicitRoutingAnnotationDecorator
- [#2794] Merge pull request [#2794] from rectorphp/cakephp-before-request
- [#2840] Inject the coveralls token as a secret, Thanks to [@ikvasnica]
- [#2797] use PhpDocInfo by default
- [#2799] DocBlockManipulator decoupling
- [#2801] Decouple DocBlockClassRenamer
- [#2790] Merge pull request [#2790] from rectorphp/cakephp-routes-to-explicit
- [#2807] Move src namespace frm Rector\ to Rector\Core\
- [#2817] decouple ParentConstantReflectionResolver
- [#2825] decouple VendorLock package
- [#2821] rector for doctrine setParameters method, Thanks to [@vladyslavstartsev]
- [#2818] decouple function node finder and collector from ParsedNodesByType
- [#2823] NodeNameResolver decoupled to own package
- [#2742] Decouple PHPStan Type to function resolver logic, Thanks to [@Lctrs]
- [#2810] rename package to use lowercased standard, prevent confusion with PSR-4
- [#2791] [cs] apply property and method order

### Deprecated

- [#2780] remove deprecated `removeBy*()` in DocBlockManipulator

### Fixed

- [#2819] Cognitive complexity fixes
- [#2813] Fix encapsed
- [#2846] Fix dev in build
- [#2808] Fix replacement for Table::buildRules(), Thanks to [@markstory]
- [#2800] Update to PHPStan 0.12.9 and fix scoping deps
- [#2812] [PHP 74] Fix ArraySpreadInsteadOfArrayMergeRector for non-constant string keys
- [#2816] PHPStan fixes, Thanks to [@ondrejmirtes]
- [#2814] Fix protected parent constant override

### Removed

- [#2831] remove parameter in imports
- [#2827] remove unused method
- [#2798] Remove nullable PhpDoc
- [#2826] remove duplicated method
- [#2787] Merge pull request [#2787] from rectorphp/remove-setter-only
- [#2789] Merge pull request [#2789] from rectorphp/remove-many-args

## [v0.6.14] - 2020-01-29

### Added

- [#2758] [CI] add SonarCube
- [#2726] [CakePHPToSymfony] Add CakePHPModelToDoctrineEntityRector
- [#2744] [CakePHPToSymfony] Add CakePHPModelToDoctrineRepositoryRector
- [#2731] [CakePHPToSymfony] Add model migration for ManyToOne, OneToOne, ManyToMany
- [#2745] [CakePHPToSymfony] Add threaded and count to CakePHPModelToDoctrineRepositoryRector
- [#2747] [CakePHPToSymfony] Add list to CakePHPModelToDoctrineRepositoryRector
- [#2735] [DX] add rd() function mapping to tracy
- [#2711] Add SetcookieRector, Thanks to [@zonuexe]
- [#2757] Added phpunit problem matcher, Thanks to [@staabm]
- [#2759] add travis retry
- [#2761] Added opcache to docker image, Thanks to [@JanMikes]

### Changed

- [#2722] Merge pull request [#2722] from rectorphp/readme-demo
- [#2736] Merge pull request [#2736] from rectorphp/generator-core
- [#2728] Merge pull request [#2728] from C0pyR1ght/patch-4, Thanks to [@C0pyR1ght]
- [#2737] Docker build secured image for online demo, Thanks to [@JanMikes]
- [#2762] Warmup opcache in docker, Thanks to [@JanMikes]
- [#2739] split workflows, badge is above repository
- [#2769] Merge pull request [#2769] from rectorphp/node-type-resolver
- [#2773] decouple PhpParserNodeMapper
- [#2772] misc
- [#2770] Cleanup
- [#2771] Decouple ArrayTypeAnalyzer, CountableTypeAnalyzer and StringTypeAnalyzer
- [#2768] Merge pull request [#2768] from rectorphp/node-type-resolver
- [#2767] Merge pull request [#2767] from rectorphp/sonarcube
- [#2750] move non-Rectors out of Rector namespace
- [#2752] Merge pull request [#2752] from rectorphp/find-collector

### Fixed

- [#2723] Fix AssertChoide with choices
- [#2741] Merge pull request [#2741] from rectorphp/fix-inter

## [v0.6.13] - 2020-01-20

### Added

- [#2720] add .travis.yml with tag release

## [v0.6.12] - 2020-01-20

- [#2704] [CI] Add check for duplicated fixture after before content
- [#2709] [CakePHPToSymfony] Add CakePHPControllerRenderToSymfonyRector
- [#2718] [CakePHPToSymfony] Add h function templates
- [#2714] Added composer rector-ci to workflow, Thanks to [@jeroensmit]

### Changed

- [#2630] [AddArrayReturnDocTypeRector] Allow mixed[] and iterable<mixed> in place of Rector's setting wrong infered types, Thanks to [@gnutix]
- [#2710] Merge pull request [#2710] from jeroensmit/splitIfs, Thanks to [@jeroensmit]
- [#2712] Merge pull request [#2712] from jeroensmit/RemoveUnusedAliasBug, Thanks to [@jeroensmit]
- [#2713] Merge pull request [#2713] from jeroensmit/CombineIfPreserveDoc, Thanks to [@jeroensmit]
- [#2703] Merge pull request [#2703] from rectorphp/dx-readme
- [#2702] Merge pull request [#2702] from rectorphp/dx-get-node-types
- [#2700] Merge pull request [#2700] from Aerendir/failing-test-case-for-2699, Thanks to [@Aerendir]
- [#2698] Merge pull request [#2698] from rectorphp/cakephp-controller-render
- [#2694] Merge pull request [#2694] from Aerendir/failing-test-case-for-2693, Thanks to [@Aerendir]
- [#2691] updated UnwrapFutureCompatibleIfRectorTest, Thanks to [@C0pyR1ght]
- [#2715] Merge pull request [#2715] from rectorphp/coverage
- [#2719] Use on published release to trigger a new release on rector-prefixed, Thanks to [@Lctrs]

### Fixed

- [#2707] Merge pull request [#2707] from rectorphp/fix-throws-void
- [#2706] Fix UnwrapFutureCompatibleIfFunctionExistsRector for no else [closes [#2691]]
- [#2708] Merge pull request [#2708] from rectorphp/fix-type-order

## [v0.6.11]

### Added

- [#2683] [PHPUnit] Add ClassMethod/RemoveEmptyTestMethodRector
- [#2692] Add PHP Linter
- [#2676] Add CheckStaticTypeMappersCommand to CI
- [#2655] Rename --rule argument into --only, add documentation., Thanks to [@gnutix]
- [#2663] Add support for stringy calls in CallReflectionResolver, Thanks to [@Lctrs]
- [#2674] Added CombineIfRector, Thanks to [@jeroensmit]
- [#2670] Add support for invokable and array callables in CallReflectionResolver, Thanks to [@Lctrs]
- [#2685] Add --output-file

### Changed

- [#2690] [PHPUnit] Improve GetMockRector
- [#2662] Good bye CallManipulator, Thanks to [@Lctrs]
- [#2654] Merge pull request [#2654] from rectorphp/polyfill-php
- [#2687] Changed ChangeMethodVisibilityRector yaml config, Thanks to [@C0pyR1ght]
- [#2657] Migrate from PHPStan's Broker to ReflectionProvider, Thanks to [@Lctrs]
- [#2658] Introduce a CallReflectionResolver, Thanks to [@Lctrs]
- [#2659] Update rector-prefixed only on push to master, Thanks to [@Lctrs]
- [#2660] Decouple PHPStanStaticTypeMapper
- [#2681] Merge pull request [#2681] from rectorphp/phpunit4
- [#2682] Merge pull request [#2682] from rectorphp/get-mock
- [#2664] Merge pull request [#2664] from rectorphp/static-type-mapper-collector
- [#2666] Merge pull request [#2666] from rectorphp/type-mapper-col-2
- [#2671] Merge pull request [#2671] from rectorphp/type-mapper-col-3
- [#2672] Merge pull request [#2672] from jeroensmit/RemoveDelegatingParentCallDefault, Thanks to [@jeroensmit]
- [#2673] Merge pull request [#2673] from staabm/patch-3, Thanks to [@staabm]

### Fixed

- [#2668] Fixed url, Thanks to [@palpalani]
- [#2686] Fix sync releases with rector-prefixed, Thanks to [@Lctrs]

## [v0.6.10] - 2020-01-12

### Added

- [#2640] [AddDoesNotPerformAssertionToNonAssertingTestRector] Add failing test for Prophecy assertions., Thanks to [@gnutix]
- [#2638] [CodingStyle] Prevent adding non-namespaced imports to non-namespaced class
- [#2546] [Php70] add a rector to pass only variables as arguments by reference, Thanks to [@Lctrs]
- [#2648] [Doctrine 2.0] Add class rename set
- [#2644] [CakePHP 3.0] add class renames
- [#2643] [CakePHP 3.0] Add AppUsesStaticCallToUseStatementRector
- [#2613] Add support for PHPStan 0.12+ [@template] annotation., Thanks to [@gnutix]
- [#2649] Add PHPStanAttributeTypeSyncer
- [#2622] added link, Thanks to [@C0pyR1ght]
- [#2623] added get started to readme, Thanks to [@C0pyR1ght]
- [#2624] Add Gmagick to Imagick set
- [#2629] Add a --no-progress-bar option (inspired from ECS) for nicer CI output., Thanks to [@gnutix]

### Changed

- [#2587] [ForeachItemsAssignToEmptyArrayToAssignRector] apply on code it should not, Thanks to [@gnutix]
- [#2610] Improve Rector success message., Thanks to [@gnutix]
- [#2618] More reliable way getting first stmt item, Thanks to [@Jaapze]
- [#2645] Extend AddDoesNotPerformAssertionToNonAssertingTestRector by catching more test messages
- [#2641] use Github Actions to compiler and publish prefixed rector.phar
- [#2635] Test with Doctrine
- [#2625] REAME changed URL to relative path, Thanks to [@C0pyR1ght]

### Fixed

- [#2636] [AddDoesNotPerformAssertionToNonAssertingTestRector] fix skipping if annotation already exists (fixes infinite loop too), Thanks to [@gnutix]
- [#2619] [CI] Fix GitHub actions phar build
- [#2637] [CountOnNullRector] fix Rector applying on properties with phpdocs array, Thanks to [@gnutix]
- [#2617] Fix PHPStan [@return] class-string<T>, Thanks to [@gnutix]
- [#2639] fix pattern miss matching
- [#2646] Fix Union Array type StaticTypeMapper to string

### Removed

- [#2633] drop redundant interface and factory, remove symfony/var-dumper from rector.phar
- [#2609] Remove stubs/ from rector.phar, Thanks to [@gnutix]

## [v0.6.9] - 2020-01-08

### Fixed

- [#2608] Fix non-direct parent foreach in ForeachItemsAssignToEmptyArrayToAssignRector

## [v0.6.8] - 2020-01-08

### Added

- [#2601] [DoctrineGemoToKnplabs] Add LoggableBehaviorRector
- [#2599] [DoctrineGemoToKnplabs] Add BlameableBehaviorRector

### Changed

- [#2605] more reliable way getting last stmt, Thanks to [@Jaapze]
- [#2603] Transition more jobs to GithubAction, Thanks to [@staabm]

### Fixed

- [#2607] [CodeQuality] Fix nested foreach case in ForeachItemsAssignToEmptyArrayToAssignRector
- [#2600] [Symfony] fix process error of controller with Internationalized routing, Thanks to [@ghostika]

## [v0.6.7] - 2020-01-07

### Added

- [#2565] [DeadCode] Add RemoveUnusedClassesRector
- [#2593] [DoctrineGedmoToKnpLabs] Add SoftDeletableBehaviorRector
- [#2569] [Polyfill] Add UnwrapFutureCompatibleIfFunctionExistsRector
- [#2570] [SOLID] Add ChangeNestedIfsToEarlyReturnRector & ChangeIfElseValueAssignToEarlyReturnRector
- [#2568] [Symfony 5] Add param types

### Changed

- [#2581] Try pcov for code coverage, Thanks to [@staabm]
- [#2572] Don't return void when function contains empty return statement, Thanks to [@snapshotpl]
- [#2575] PHPStan 0.12.4 compatibility updates, Thanks to [@ondrejmirtes]
- [#2576] travis: try phpdbg
- [#2598] Skip ReturnTypeDeclarationRector when the type is already defined as \Traversable, Thanks to [@gnutix]
- [#2582] avoid direct container in the code
- [#2584] Update compiler with PHPStan 0.12.4 workflow change
- [#2589] Transition some travis checks to GithubActions, Thanks to [@staabm]
- [#2592] Transition fatal-error scan to GithubAction, Thanks to [@staabm]
- [#2583] Revert "Try pcov for code coverage"

### Fixed

- [#2586] fix box autoload Neon class in scoper.php.inc by --no-parallel
- [#2588] Fix PHPStan 0.12+ [@implements] and [@extends] class annotations., Thanks to [@gnutix]
- [#2595] Fix running AddArrayReturnDocTypeRector on empty arrays, Thanks to [@gnutix]
- [#2566] fix ChangeArrayPushToArrayAssignRector for multiple items

### Removed

- [#2591] Remove travis-CI jobs which were moved to GithubAction, Thanks to [@staabm]
- [#2567] make nested chain call remove configurable

## [v0.6.6] - 2020-01-04

### Added

- [#2557] [CodeQuality] Add ChangeArrayPushToArrayAssignRector
- [#2559] [CodeQuality] Add ForRepeatedCountToOwnVariableRector
- [#2561] [CodingQuality] Add ForeachItemsAssignToEmptyArrayToAssignRector
- [#2558] [MinimalScope] Add ChangeLocalPropertyToVariableRector
- [#2538] [Php71] Add failing test case for CountOnNullRector, Thanks to [@gnutix]
- [#2548] add IterableType to StaticTypeMapper::mapPHPStanTypeToPHPStanPhpDocType()

### Changed

- [#2541] [Restoration] Preconfigure CompleteImportForPartialAnnotationRector
- [#2562] Prevent variable name override
- [#2553] Improve 3rd party property type resolution
- [#2550] allow loading bleedingEdge.neon config inside PHPStan phar
- [#2563] prevent variable scope changing

### Fixed

- [#2547] fix function callback in assert callback

## [v0.6.5] - 2020-01-03

### Added

- [#2524] [CodeQuality] Add AbsolutizeRequireAndIncludePathRector
- [#2510] [CodeQuality] Add IntvalToTypeCastRector
- [#2523] [CodeQuality] Add ArrayKeyExistsTernaryThenValueToCoalescingRector
- [#2528] [SOLID] Add RemoveAlwaysElseRector
- [#2536] [TypeDeclaration] Add support for PhpStan's class-string type in PropertyTypeDeclarationRector, Thanks to [@gnutix]
- [#2505] add hasByType() method to PhpDocInfo
- [#2514] [Doctrine/dbal] Add 2.10 and 3.0 sets
- [#2517] add resource type to StaticTypeMapper
- [#2519] README: add docs for [#2087]
- [#2503] [Gedmo to Knp] Add Translatable Behavior Rector
- [#2526] add ctor only test-case
- [#2529] Added phpdoc, Thanks to [@staabm]
- [#2532] [PHP 7.4] Add @var removal to TypedPropertyRector

### Changed

- [#2530] [CodingStyle] Skip re-escaping chars by SymplifyQuoteEscapeRector
- [#2527] [DeadCode] RemoveUnusedElseForReturnedValueRector
- [#2534] improve generic type conversion
- [#2531] Apply Rector on itself
- [#2512] Update Travis
- [#2509] Do not suggest typed property when defined in vendored parent, Thanks to [@ruudk]

### Fixed

- [#2533] [CodeQuality] Fix identical boolcast
- [#2520] [CodingStyle] Fix IdenticalFalseToBooleanNotRector for null|bool
- [#2518] [TypeDeclaration] Fix static property type resolution
- [#2511] Fix single-line comment and constant scalar type match
- [#2508] Fix param type union
- [#2507] Fix AddArrayReturnDocTypeRector for existing comment

## [v0.6.4] - 2019-12-27

### Added

- [#2497] [DeadCode] Add TernaryToBooleanOrFalseToBooleanAndRector
- [#2496] [Nette] Add magic template code quality
- [#2500] [PHP] add PHP version feature checks, Thanks to [@fsok]
- [#2484] [Gedmo to Knp] Add TreeBehaviorRector
- [#2476] Add ScanFatalErrors command
- [#2479] prevent re-adding id at AddEntityIdByConditionRector

### Changed

- [#2482] [DoctrineGedmoToKnplabs] init set
- [#2502] Apply HelperFunctionToConstructorInjectionRector only in non-static class method scope
- [#2475] [RemoveEmptyClassMethodRector + RemoveDeadConstructorRector] Should not apply on protected/private constructors, Thanks to [@gnutix]
- [#2478] Link a few more recent articles, Thanks to [@staabm]
- [#2480] merge ParentTypehintedArgumentRector to AddParamTypeDeclarationRector
- [#2481] init MoveValueObjectsToValueObjectDirectoryRector
- [#2487] Skip non-variable non-scalars in BinaryOpBetweenNumberAndStringRector

### Fixed

- [#2485] Fix grouped use statement import
- [#2486] Fix alias object conflict with existing type
- [#2483] Fix typos, Thanks to [@staabm]
- [#2501] Fix return override in case of parent vendor lock
- [#2489] Fix name resolution in ArrayKeyFirstLastRector
- [#2491] Fix codesample in TypedPropertyRector, Thanks to [@ruudk]
- [#2493] fix PREG_SPLIT_DELIM_CAPTURE for split Nette Utils
- [#2499] Fix function override under namespace
- [#2492] [docs] Fix codesample in TypedPropertyRector, Thanks to [@ruudk]

## [v0.6.3] - 2019-12-23

### Added

- [#2457] [Class_] Add AddInterfaceByTraitRector
- [#2463] [Doctrine] Add AddEntityIdByConditionRector
- [#2465] [PHP Deglobalize] Add ChangeGlobalVariablesToPropertiesRector
- [#2461] Added int to StaticTypeMapper, Thanks to [@lulco]
- [#2458] prevent duplicated added interface
- [#2472] add symfony5 set

### Changed

- [#2464] [Nette] Control to Symfony Form + Controller
- [#2470] merge Rector arguments on import
- [#2459] disable imports by default

### Fixed

- [#2466] [Symfony] fix dot in GetParameterToConstructorInjectionRector
- [#2467] fix rename method call

## [v0.6.2] - 2019-12-18

### Added

- [#2439] [PHPUnit] Add get_class double sided to AssertCompareToSpecificMethodRector
- [#2447] [TypeDeclaration] Add AddParamTypeDeclarationRector
- [#2450] add intersection type support to StaticTypeMapper
- [#2448] [PHP 7.0] Add StaticCallOnNonStaticToInstanceCallRector edge case of property fetch static call
- [#2437] [Phalcon 4.0] Add FlashWithCssClassesToExtraCallRector

### Changed

- [#2442] [Symfony] refactor to ServiceMap
- [#2438] Make BarewordStringRector skip missing file
- [#2436] Update .travis.yml, Thanks to [@andreybolonin]
- [#2428] Document import_short_classes + import_doc_blocks, Thanks to [@gnutix]
- [#2427] Update phalcon40.yaml, Thanks to [@ruudboon]

### Fixed

- [#2435] Various fixes
- [#2420] Fix NewToStaticCallRector documentation, Thanks to [@RusiPapazov]

## [v0.6.1] - 2019-12-10

**Rector is now shipped as prefixed phar - download from [rector-prefixed](https://github.com/rectorphp/rector-prefixed)**

### Added

- [#2410] Added default to prevent rector from breaking, Thanks to [@jeroensmit]
- [#2409] [Phalcon 4] Add SwapClassMethodArgumentsRector rule
- [#2407] Added missing methods, Thanks to [@ruudboon]
- [#2406] Added check for correct delimiter to use in preg_quote, Thanks to [@jeroensmit]
- [#2369] Add typo auto import + Swiftmailer 60 set
- [#2397] Add rector rule for EntityInterface::isNew(), Thanks to [@markstory]
- [#2373] Another attempt to add Compiler + upgrade to PHPStan 0.12
- [#2374] Add another deprecation to the cakephp40 set., Thanks to [@markstory]
- [#2392] Fix CompleteDynamicPropertiesRector to add parent property

### Changed

- [#2394] [TypeDeclaration] Object without class
- [#2414] StaticTypeMapper - missing boolean type, Thanks to [@sojki]
- [#2386] chore: use php 7.4 cli, Thanks to [@danielroe]
- [#2359] Prevent crashing on dead symlinks, Thanks to [@jeroensmit]
- [#2378] [Phalcon 4.0] init
- [#2389] composer: lock phpstan to 0.11.19 due to breaking changes [closes [#2385]]
- [#2390] Make sure name is passed to getName(), instead of expr
- [#2396] prevent union sub-type nullable override
- [#2395] prevent union sub-type nullable override
- [#2400] return false on MethodCall name to prevent expr errors
- [#2391] Exludes stubs on package install to prevent PHPStorm confussion

### Fixed

- [#2372] [Symfony] Fix ConsoleExecuteReturnInt for nested functions
- [#2393] [TypeDeclaration] Fix returned yield nodes in nested function [closes [#2381]]
- [#2371] Update FilesFinder.php to fix builds..., Thanks to [@mallardduck]
- [#2411] Fix undefined offset in UseInterfaceOverImplementationInConstructorRector, Thanks to [@jeroensmit]
- [#2368] Fix: RandomFunctionRector typo fix + regenerated docs, Thanks to [@radimvaculik]
- [#2404] Fix - iterable type introduced in PHP 7.1, Thanks to [@sojki]
- [#2358] fix var type on method call

## [v0.6.0] - 2019-11-26

### Added

- [#2347] Add diff based execution, Thanks to [@EmanueleMinotto]

### Changed

- [#2350] [NetteToSymfony] Extend migration set
- [#2351] Open "create" command to the public
- [#2346] [PHP] ContinueToBreakInSwitchRector skip continue with argument >1, Thanks to [@fsok]
- [#2344] Bump to Symfony 4.4/5 and PHP 7.2+
- [#2343] allow Symfony 5, bump min to Symfony 4.4

### Fixed

- [#2353] Fixed error on classConstFetch outside class, Thanks to [@jeroensmit]
- [#2352] Fix description, Thanks to [@staabm]
- [#2349] [Php 70] Fix this call on static for PHPUnit non-assert

## [v0.5.23] - 2019-11-20

### Added

- [#2332] [PHPUnit][Symfony] Add jakzal-injetor Rector
- [#2338] [ID to UUID] Add middle step to initalize default uuid value
- [#2337] Add more refactorings for CakePHP 4, Thanks to [@markstory]
- [#2331] [PHP 8.0] Add union types
- [#2329] [PHP Deglobalize] Add ChangeGlobalVariablesToPropertiesRector

### Fixed

- [#2341] fix magic static

## [v0.5.22]

### Added

- [#2302] [DX] add "paths" parameter
- [#2264] [DynamicTypeAnalysis] Add Dynamic type infering
- [#2278] travis: change ENV matrix to jobs + add Windows OS
- [#2310] Added docs check, Thanks to [@jeroensmit]
- [#2326] [PHPUnit 8.0] Add ReplaceAssertArraySubsetWithDmsPolyfillRector
- [#2273] Add a working and a failing test case for [#2187], Thanks to [@gnutix]
- [#2321] [Nette 3.0]  Add Nette 2.x to Nette 3 upgrade set

### Changed

- [#2297] Bump to PHP Parser 4.3
- [#2306] improve stmt count check
- [#2257] Update return types when set to array, Thanks to [@stedekay]
- [#2325] [DX] report missing rules in `exclude_rectors` parameter
- [#2311] [DeadCode] Class constant with trait, Thanks to [@jeroensmit]
- [#2291] [PHP70] Skip PHPUnit assert in `ThisCallOnStaticMethodToStaticCallRector`
- [#2328] simplify `ImportSkipper` skip for ClassLike name
- [#2277] Update rule with proper syntax of `ReturnArrayClassMethodToYieldRector`, Thanks to [@gnutix]
- [#2315] bugfix(Symfony33); correct the replacement of a namespace, Thanks to [@nissim94]
- [#2292] check variable name
- [#2300] Give testset a name, Thanks to [@jeroensmit]
- [#2309] Merged `RemoveDeadZeroAndOneOperationRector` and` RemoveZeroAndOneBinarRector`, Thanks to [@jeroensmit]

### Fixed

- [#2282] [CodeQuality] Fix return type copy
- [#2284] [CodingStyle] Fix extra new-line for EncapsedString
- [#2324] [PHPUnit] Fix array subset for non-scalar values
- [#2288] fix double import of function names
- [#2289] fix name resolving on variable
- [#2269] fix false static type of Symfony\SplFileInfo getRealPath()
- [#2293] RemoveSetterOnlyPropertyAndMethodRector and UnusedPrivatePropertyRector fixes, Thanks to [@jeroensmit]
- [#2294] Importing fix
- [#2299] Fixed removing constructor when parameter defaults are different, Thanks to [@jeroensmit]
- [#2323] Fixed sed command, Thanks to [@jeroensmit]
- [#2318] Fixed issue with sed command:, Thanks to [@jeroensmit]
- [#2275] fix no-regular naming
- [#2308] Fix removing 0 when on left side of Minus, Thanks to [@jeroensmit]
- [#2327] Fix parent interface, extends, implements same name as short name
- [#2317] code fixes
- [#2281] Fix ECS for windows, Thanks to [@orklah]

## [v0.5.21] - 2019-11-05

### Added

- [#2254] [CI] Add nette-utils-code-quality set
- [#2255] [CI] Add coding-style set
- [#2246] [CodeQuality] Add property assigns to RemoveAlwaysTrueConditionSetInConstructorRector
- [#2240] add template nested
- [#2223] Add AbstractController as base class if no one exists, Thanks to [@stedekay]

### Changed

- [#2234] Decouple PropertyFetchManipulator methods for array dim fetch
- [#2248] decouple phpunit 50 set
- [#2237] keep comment on type change
- [#2207] Previous statement rewrite, Thanks to [@jeroensmit]
- [#2231] make screen generate file by default
- [#2258] run all sets
- [#2238] infer php version from composer.json
- [#2252] Import default
- [#2253] Speedup
- [#2239] Better anonymous class handling, Thanks to [@jeroensmit]
- [#2259] [tests] use generic method over explicit fixture yield

### Fixed

- [#2249] [CodeQuality] Fix else in SimplifyForeachToArrayFilterRector
- [#2236] [TypeDeclaration] Fix CompleteVarDocTypePropertyRector for mixed[] override
- [#2261] Fix provider
- [#2235] Fix importing parent that is identical in short to class name
- [#2251] fix standalone run to symfony demo
- [#2250] Fixed edge cases of RemoveDeadStmtRector, Thanks to [@jeroensmit]
- [#2247] Fixed removal of non expressions, Thanks to [@jeroensmit]
- [#2262] Fixes
- [#2263] Fix StaticTypeMapper for nullables
- [#2214] travis dogfood replay, Thanks to [@ktomk]
- [#2203] optimize ConsoleExecuteReturnIntRector
- [#2211] Ignore not required files in docker build, Thanks to [@JanMikes]
- [#2222] Enable code quality set
- [#2196] Update grammar in the README for clarity, Thanks to [@sbine]
- [#2220] [cs] re-order private methods by call order

### Removed

- [#2233] Also remove assignment if the value of the assignment is different, Thanks to [@jeroensmit]

## [v0.5.20] - 2019-10-31

### Added

- [#2224] add dead-code set to CI
- [#2197] Add test to ConsoleExecuteReturnIntRector for a not command class, Thanks to [@franmomu]
- [#2221] Add Rector-CI and handy "sets" parameter
- [#2206] [PHPUnit 7.5] Add WithConsecutiveArgToArrayRector

### Fixed

- [#2202] Fix ConsoleExecuteReturnIntRector if target class not directly extends Command, Thanks to [@keulinho]
- [#2198] Fixed TYPO3 community package link, Thanks to [@JanMikes]
- [#2218] Fix empty -c/--config value
- [#2217] FIX AddDoesNotPerformAssertionToNonAssertingTest, Thanks to [@DaveLiddament]
- [#2200] Fix ConsoleExecuteReturnIntRector for non console commands, Thanks to [@keulinho]

## [v0.5.19] - 2019-10-24

### Added

- [#2195] [Laravel] Add Laravel 6 instant upgrade set
- [#2192] Add test case for route annotation with optional parameters, Thanks to [@stedekay]
- [#2182] [PHP 74] Add ChangeReflectionTypeToStringToGetNameRector

### Fixed

- [#2193] fix union too many types
- [#2190] Various Return types fixes
- [#2194] fix for Template and Route annotation
- [#2191] prevent mixed of specific override

## [v0.5.18] - 2019-10-22

### Added

- [#2177] [CodeQuality] Add ArrayMergeOfNonArraysToSimpleArrayRector
- [#2176] [CodeQuality] Add AddPregQuoteDelimiterRector
- [#2184] [FEATURE] Possibility to add custom phpstan.neon configuration, Thanks to [@sabbelasichon]
- [#2172] Add rules for ConsoleIo::styles(), Thanks to [@markstory]
- [#2175] Added rector sets composition hint, Thanks to [@SilverFire]
- [#2181] add test case for [#2158]
- [#2188] add Standalone Runner

### Fixed

- [#2180] fix ArgumentAdderRector for anonymous class [closes [#2157]]
- [#2183] fix RemoveUnusedAliasRector for doc vs class concurency
- [#2174] Fix for Issue2173, Thanks to [@dpesch]
- [#2169] Fixes
- [#2168] Prevent CI floods with progress bar

## [v0.5.17] - 2019-10-15

### Added

- [#2087] Added way to exclude rectors, Thanks to [@jeroensmit]
- [#2156] [PHPUnit] [Doc] Add FixDataProviderAnnotationTypoRector
- [#2155] [PHPUnit] [Doc] Add EnsureDataProviderInDocBlockRector
- [#2144] [PHPUnit] Add RemoveDataProviderTestPrefixRector
- [#2132] [Symfony] Add ConsoleExecuteReturnIntRector, Thanks to [@keulinho]
- [#2166] add paypal backers
- [#2150] phpstan - add getContainer() after boot()
- [#2141] add inter support
- [#2142] [PHPUnit 6] Add AddDoesNotPerformAssertionToNonAssertingTestRector
- [#2087] Added way to exclude rectors, Thanks to [@jeroensmit]

### Fixed

- [#2146] fix ValueResolver static array for non static keys
- [#2152] fix get property by class
- [#2114] Fix for issue [#2090], Thanks to [@jeroensmit]
- [#2140] fix 3rd party testing without config
- [#2147] fix string-named func
- [#2164] [code-quality] fixes processing of trait - RemoveAlwaysTrueConditionSetInConstructorRector  [#2162], Thanks to [@lapetr]
- [#2165] Bugfix for RemoveUnreachableStatementRector, Thanks to [@jeroensmit]

### Changed

- [#2145] [DeadCode] Make RemoveDefaultArgumentValueRector skip native functions
- [#2148] [TypeDeclaration] Prevent array-iterable-Iterator override in ReturnTypeDeclarationRector
- [#2159] Screen file command improvements
- [#2149] prevent doc type of child array override
- [#2151] make `ReturnTypeDeclarationRector` keep implementation

## [v0.5.16] - 2019-10-10

- [#2139] Add `screen-file` command for learning & trainings

## [v0.5.15] - 2019-10-10

### Fixed

- [#2135] Fix EncapsedStringsToSprintfRector for non-var exprs
- [#2130] fix stub loading location
- [#2128] Fix `EncapsedStringsToSprintfRector` when using class properties, Thanks to [@gnutix]

## [v0.5.14] - 2019-10-09

### Added

- [#2123] [SOLID] Prefer interface if possible
- [#2115] [DeadCode] Add `SimplifyIfElseWithSameContentRector`
- [#2080] [DeadCode] Add `RemoveUnreachableStatementRector`
- [#2103] [StrictCodeQuality] Add `VarInlineAnnotationToAssertRector`
- [#2122] [StrictCodeQuality] Add freshly created node support to var inline assert
- [#2047] Add conditional method renaming rector, Thanks to [@markstory]
- [#2094] Add `ShortenElseIfRector`, Thanks to [@keulinho]
- [#2095] Add fixture for phpunit x>y to greaterThan refactoring, Thanks to [@keulinho]
- [#2096] `ImportFullyQualifiedNamesRector` : add a failing test about modified annotations that shouldn't be, Thanks to [@gnutix]
- [#2084] Add failing test case for `ReturnArrayClassMethodToYieldRector` (removing comments), Thanks to [@gnutix]
- [#2099] Add documentation for `ImportFullyQualifiedNamesRector` new argument, Thanks to [@gnutix]
- [#2100] Add FunctionCallToConstantRector, Thanks to [@keulinho]
- [#2081] Add failing test: StringifyStrNeedlesRector adds (string) to a method call that returns a string anyway, Thanks to [@gnutix]
- [#2077] Add an option to skip importing root namespace classes (like \DateTime), Thanks to [@gnutix]
- [#2091] Add `UseIncrementAssignRector`, Thanks to [@keulinho]
- [#2074] Add a PHPUnit TestCase stub., Thanks to [@gnutix]
- [#2073] `StringToArrayArgumentProcessRectorTest` : add failing test around Traversable, Thanks to [@gnutix]
- [#2062] Add stubs instead of dump class replace in constructor
- [#2052] Add post run name imports
- [#2049] Added StrictArraySearchRector - Issue [#2009], Thanks to [@jeroenherczeg]
- [#2124] add CommanderCollector
- [#2054] [DoctrineCodeQuality] Initialize collections in constructor

### Changed

- [#2065] [CodingStyle] Allow private ctor override for static factory
- [#2067] Use contextual method rename rector in cake4 rules., Thanks to [@markstory]
- [#2125] improve uuid steps
- [#2107] restart changed doc
- [#2097] Improve class annotation matching
- [#2109] skip var type in anonymous class for `PropertyTypeDeclarationRector`
- [#2083] Do not apply `SimplifyIfReturnBoolRector` when there are comments in between the if statements, Thanks to [@gnutix]
- [#2072] [CodingStyle] Skip common annotation aliases in RemoveUnusedAliasRector

### Fixed

- [#2121] Allow `@Template` to get nullable values
- [#2076] [Symfony] Fix is submitted
- [#2093] fix stringy str needless for return strings
- [#2112] fix joinColumns always fallback
- [#2070] Fixed the third argument in `VarDumperTestTrait`, Thanks to [@adrozdek]
- [#2075] Fix ReflectionException (Method `PHPUnit\Framework\TestCase::tearDown(...)` does not exist) thrown while autoloading class Symfony\Bundle\FrameworkBundle\Test\WebTestCase., Thanks to [@gnutix]
- [#2088] Fix changing the wrong property fetches, Thanks to [@jeroensmit]
- [#2078] Fix type resolution for traversable
- [#2079] fix [@TODO] malfforms
- [#2102] Fix countable for countable classes without countable
- [#2101] Fix virtual property
- [#2082] Fixing `return new static()` not being covered by `MakeInheritedMethodVisibilitySameAsParentRector`, Thanks to [@gnutix]
- [#2060] [PHP 7.1] Skip extra argument removal for parent static call

## [v0.5.13] - 2019-09-27

### Added

- [#1980] [ZendToSymfony] Init Zend 1 to Symfony 4
- [#1982] [Autodiscovery] init
- [#2044] [CodeQuality] Add RemoveSoleValueSprintfRector
- [#2032] [CodingStyle] Add MakeInheritedMethodVisibilitySameAsParentRector
- [#2033] [CodingStyle] Add CallUserFuncCallToVariadicRector
- [#1978] [DX] add check class existence scripts
- [#1924] [DeadCode] Add RemoveAlwaysTrueIfConditionRector
- [#2012] [Doctrine] Add ChangeReturnTypeOfClassMethodWithGetIdRector
- [#1928] [Doctrine] Add stubs instead of full orm dependencies
- [#2003] [Doctrine] add CustomIdGenerator + step 2 for uuid Doctrine migration
- [#1994] [Doctrine] Add AlwaysInitializeUuidInEntityRector
- [#2031] [Monolog] Add 2.0 upgrade set
- [#1984] [PHPStan] add PreventParentMethodVisibilityOverrideRule
- [#1947] [PHPUnit] Add [@see] annotation to reference test
- [#1948] [PHPUnit] Add array call to data provider
- [#2035] [Php53] Add DirNameFileConstantToDirConstantRector [close [#2006]]
- [#2037] [Php71] Add ListToArrayDestructRector
- [#2040] [Rector] Add RemoveZeroBreakContinueRector
- [#2034] [Restoration] Add MissingClassConstantReferenceToStringRector
- [#2020] [Symfony] Add MergeMethodAnnotationToRouteAnnotationRector
- [#2019] [TypeDeclaration] Add AddMethodCallBasedParamTypeRector
- [#2000] add isInDoctrineEntityClass() method
- [#2001] add changeName/getName to serializer
- [#1968] add skip of one to one relations with mapped by
- [#1998] make DocBlockManipulator protected in AbstractRector + add GeneratedValue annotatoin parsing
- [#1955] Added stub directory to Docker composer build phase, Thanks to [@JanMikes]
- [#2015] Add rules for renaming CakePHP's Router methods, Thanks to [@ADmad]
- [#2048] Add support for various annotation formats
- [#1927] Add Sensio TemplateTagValueNode
- [#1933] add removeNodeFromStatements() method to remove statement without key easily
- [#2039] add function aliases to celebrity
- [#1921] Add uuid only to entities with id
- [#1939] Add ReturnedNodesReturnTypeInferer + big \*TypeDeclarationRector refactoring

### Changed

- [#2036] [Php72] improve UnsetCastRector
- [#2017] [Renaming] init new package
- [#2030] [Renaming] fqnize freshly namespaced class
- [#1943] Improve covariance in ReturnTypeDeclarationRector
- [#1992] improve join table patterns
- [#1952] improve test case provided rector class debug info
- [#1937] uuid rules are now designed to be used at once
- [#1925] rename level to set to prevent confusion of duplicate
- [#2046] Decouple annotation to own PhpDocNodeFactory to allow extension without change
- [#1869] Refactoring order creates incompatible return types, Thanks to [@scheb]
- [#1986] improve single info multiline doc, drop NodeDecorator
- [#1976] [phpstorm meta] make getByType() return nullable
- [#1993] Improve annotation content joins
- [#1996] Optimize class renaming
- [#1997] Improve annotation spacing
- [#1972] Migrate tests to data providers
- [#1971] Cleanup
- [#2043] [CI] run all sets check
- [#1979] [DX] check invalid config arguments
- [#2011] [Doctrine] Step [#3] - `getUuid`/`setUuid` method calls to id values
- [#1966] [Doctrine] split id and relation migration to 2 steps
- [#2016] [Php] Split to own packages by version
- [#1989] [PhpDoc] Multiline test improvements
- [#2045] [Symfony] Make MakeDispatchFirstArgumentEventRector work with get_class
- [#1938] [TypeDeclaration] Extend ReturnTypeDeclarationRector with incorrect types override
- [#1931] Make type replacement of annotatoin OOP
- [#1932] make use of `getProperties()`, `getMethods()`, `getConstants()` and `getTraitUses()`
- [#1914] Create rector for transforming Laravel validation rules to a prettier format, Thanks to [@sashabeton]
- [#1923] `RemoveUnusedPrivatePropertyRector` should skip entities [closes [#1922]]
- [#1940] cleanup type resolving
- [#1934] UUID report old to new table
- [#1961] Migrate `TypeInferers` and `TypeResolvers` to PHPStan object types
- [#1991] make `EntityUuidNodeFactory` extensible
- [#2002] cover name at `JoinColumn` removal
- [#1999] make column tag value node changeable
- [#1957] Move from string types to PHPStan types
- [#1946] from helper methods to isStaticType() with PHPStan object typing
- [#1953] StaticTypeMapper refactoring
- [#1866] Misc
- [#2013] cleanup
- [#1951] Update README.md, Thanks to [@drbyte]

### Fixed

- [#2021] [PHP71] Fix BinaryOpBetweenNumberAndStringRector for variables
- [#1969] fix spacing with SpacelessPhpDocTagNode
- [#2018] fix renaming class to existing one [closes [#1438]]
- [#2014] Code sample fixes, Thanks to [@HypeMC]
- [#2024] fix parent typehint for anonymous class
- [#2025] Fix numeric string type in BinaryOpBetweenNumberAndStringRector
- [#2026] Fix pseudo namespace to namespace with use statement
- [#1967] Fix Doctrine stubs + separate reported files into 2
- [#2027] Fix printing of tab-indented files
- [#1962] Fixed small typos for Symfony docs., Thanks to [@adrozdek]
- [#2022] Fix anonymous class constant
- [#1990] fix multi constaints
- [#1983] Fix Return type Covariance Inverse Order
- [#1926] [DeadCode] Keep parent call delegation in case of accessibility override
- [#2023] [DeadCode] Skip magic property `RemoveUnusedPrivatePropertyRector`

### Removed

- [#1954] remove `CallableCollectorPopulator`
- [#1935] remove `getDoctrine\*()` methods from `PhpDocInfo`, use `getByType()` instead
- [#1958] remove few PHP-Parser rules to prevent package-rules vs package-features confusion

## [v0.5.12] - 2019-08-29

### Added

- [#1898] Start CakePHP 4.0 rectors, Thanks to [@markstory]
- [#1902] [BetterPhpDocParser] Add support for parsing Doctrine annotations
- [#1906] [DoctrinePhpDocParser] Add relation tags and join column
- [#1910] Add more rectors for CakePHP 4.0, Thanks to [@markstory]
- [#1916] add parent construct call to uuid init
- [#1912] [Doctrine] Id to UUID migration

### Changed

- [#1903] [dx] make ShouldNotHappen exceptions more informative
- [#1915] allow non-uuid props
- [#1897] allow testing outside Rector
- [#1901] Move Jetbrains PhpStorm stubs into dev dependencies, Thanks to [@atierant]
- [#1908] let parse only Doctrine tags we need

### Fixed

- [#1917] fix expected namespace

## [v0.5.11] - 2019-08-25

### Added

- [#1880] [DeadCode] Add RemoveNullPropertyInitializationRector to dead-code set
- [#1865] [PSR4] Add NormalizeNamespaceByPSR4ComposerAutoloadRector
- [#1895] add makeFinal() method to AbstractRector
- [#1889] Add cakephp3.8 target and fix a typo, Thanks to [@markstory]
- [#1847] [PHP 7.4] Add literal thousand superator

### Changed

- [#1878] [PSR4] Improve renamed classes collector to sort by highest parent
- [#1894] rename levels command to sets
- [#1896] merge isName and isNameInsensitive

### Fixed

- [#1885] [CodingStyle] Fix ImportFullyQualifiedNamesRector for imported namespace
- [#1888] Fix missing args in PreferThisOrSelfMethodCallRector
- [#1891] Fix fqn doc with alraedy PHP imported namespace
- [#1882] [CodingStyle] Import short classes as well [ref #1877]
- [#1883] [CodingStyle] Make import `ImportFullyQualifiedNamesRector` include same short class in same namespace
- [#1881] [RenameClassRector] Include [@ORM], [@Assert], [@Serializer] etc annotations
- [#1884] Parent constant visibility when it is declared in a super-superclass, Thanks to [@scheb]

### Removed

- [#1870] [DeadCode] Remove null value from property, Thanks to [@jacekll]
- [#1875] Remove default excluded file patterns (closes [#1815]), Thanks to [@scheb]

## [v0.5.10] - 2019-08-19

### Added

- [#1855] [CodingStyle] Add `AddArrayDefaultToArrayPropertyRector`
- [#1800] [DeadCode] Add `RemoveUnusedDoctrineEntityMethodAndPropertyRector`
- [#1819] [DeadCode] Add `RemoveSetterOnlyPropertyAndMethodCallRector`
- [#1823] [Nette] Add `JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector`
- [#1857] [TypeDeclaration] Add `AddArrayReturnDocTypeRector`
- [#1856] [TypeDeclaration] Add `AddArrayParamDocTypeRector`
- [#1850] add reporting extension, rename rector finish to finishing
- [#1818] add removed nodes collector
- [#1826] add concat + multiline case to `ManualJsonStringToJsonEncodeArrayRector`
- [#1825] add implode support to `ManualJsonStringToJsonEncodeArrayRector`
- [#1802] add iterable return type for yield values in `ReturnTypeDeclarationRector`
- [#1851] Fix FluentReplaceRector for more than 2 calls + add * matching support
- [#1807] add alias support to PropertyTypeDeclarationRector
- [#1844] add RectorFinishExtensionRunner

### Changed

- [#1828] allow multiline empty spaces strings
- [#1841] class manipulator now returns Property on property name search
- [#1814] Improve PHPStan trait scope resolving
- [#1862] [TypeDeclaration] Various `AddArrayReturnDocTypeRector` improvements
- [#1805] resolve target entity from same namespace
- [#1858] Always keeps array in `*TypeInfo`
- [#1854] Ignores resource also when type is nullable, Thanks to [@tigitz]
- [#1793] Break class name in `@var` when relation is defined in same namespace, Thanks to [@snapshotpl]
- [#1852] Fix decimal to float
- [#1806] use DateTimeInterface over `DateTime`
- [#1839] skip ManyToOne properties in `SetterOnlyMethodAnalyzer` ([#1838])
- [#1829] skip same-namespace-short name in `ImportFullyQualifiedNamesRector`
- [#1827] simplify `ManualJsonStringToJsonEncodeArrayRector`
- [#1821] skip abstract parent methods in `RemoveUnusedDoctrineEntityMethodAndPropertyRector`
- [#1838] skip ManyToOne properties in `SetterOnlyMethodAnalyzer`
- [#1840] Constants declared in interfaces have to be public, Thanks to [@scheb]
- [#1863] merge ArrayPropertyDefaultValueRector to superior `AddArrayDefaultToArrayPropertyRector`
- [#1842] Overriding constants require at least the parent's visibility, Thanks to [@scheb]
- [#1813] dont load phpstan-phpunit if phpunit not installed, Thanks to [@slepic]
- [#1808] Correct `NameResolver::getName()` + cleanup static analysis

### Fixed

- [#1830] Fix non-same parent method name for RemoveParentCallWithoutParentRector
- [#1804] Fix nullable array type param for PropertyTypeDeclarationRector
- [#1803] Fix nullable for xToOne annotation by default
- [#1794] Fix method call type
- [#1864] Fix type resolutoin in PropertyNodeParamTypeInferer
- [#1817] Fix analysed files for PHPStan scope resolver
- [#1831] Fix unused method type for return type
- [#1837] Fix RemoveSetterOnlyPropertyAndMethodCallRector race condition
- [#1853] Fix different method call return in FluentReplaceRector
- [#1859] Fix lowercase of union fqn types
- [#1832] Fix args miss-match in RemoveDelegatingParentCallRector
- [#1845] HelperFunctionToDependencyInjectionRector fix, Thanks to [@sashabeton]
- [#1836] Fixing NodeRemovingVisitor
- [#1835] Fix reseting of removed nodes
- [#1833] Fix var/method call resolver


## [v0.5.9] - 2019-08-01

### Added

- [#1761] [CodeQuality] Add ThrowWithPreviousExceptionRector
- [#1762] [CodingStyle] Add ManualJsonStringToJsonEncodeArrayRector
- [#1760] [DeadCode] Add RemoveDuplicatedCaseInSwitchRector
- [#1776] [NodeTypeResolver] Add phpunit extension
- [#1781] [TypeDeclaration] Add PropertyTypeDeclarationRector
- [#1774] add empty array to static type to string resolver
- [#1786] add priority to PropertyTypeInfererInterface and put doctrine infering first
- [#1789] add xToOne relation support to Doctrine var type resolver
- [#1787] add return nullable type to GetterOrSetterPropertyTypeInferer

### Changed

- [#1769] [Restoration] Return removed class annotations
- [#1788] infer from [@return] doc type in PropertyTypeDeclaratoin

### Fixed

- [#1782] [Symfony] Fix frozen parameter bag in DefaultAnalyzedSymfonyApplicationContainer
- [#1779] FIXED: Catastrophic backtracking in regular expression if the current…, Thanks to [@hernst42]
- [#1772] fix type analyzer for FQN
- [#1790] fix laravel53 config

### Removed

- [#1791] [CodingStyle] remove extra break from BinarySwitchToIfElseRector
- [#1780] [NodeTypeResolver] drop duplicated generic array type

### Unknown Category

- [#1764] [Symfony] Use Symfony bridge interface for `doctrine` service, Thanks to [@stloyd]
- [#1759] [SymfonyCodeQuality] From listener to subscriber
- [#1777] make constant array types unique
- [#1771] skip non-annotation prefix

<!-- dumped content end -->

## [v0.5.8] - 2019-07-21

### Added

- [#1691] [Architecture] Add `ConstructorInjectionToActionInjectionRector`
- [#1689] [CodeQuality] Add `is_a` with string true
- [#1754] [CodeQuality] Add `RemoveAlwaysTrueConditionSetInConstructorRector`
- [#1690] [CodeQuality] Add `StrlenZeroToIdenticalEmptyStringRector`
- [#1722] [CodingStyle] Add `EncapsedStringsToSprintfRector`
- [#1717] [DeadCode] Add static, self and FQN type to `RemoveUnusedPrivateMethodRector`
- [#1671] [Doctrine] Add registry to EM
- [#1693] [Doctrine] Add `RemoveRepositoryFromEntityAnnotationRector`
- [#1709] [FuncCall] Don't add `$result` to `parse_str` if second parameter is already set, Thanks to [@ravanscafi]
- [#1720] [Generic] Add `ServiceGetterToConstructorInjectionRector`
- [#1676] [PHP] Add scope limitation to `ArgumentAdderRector` for 3party non-existing params
- [#1695] [PHPStan] Add `RemoveNonExistingVarAnnotationRector`
- [#1696] [PHPUnit][Symfony] Add `AddMessageToEqualsResponseCodeRector`
- [#1744] add reference support to `ParamTypeDeclarationRector`
- [#1694] Add `rector.yaml` to `.dockerignore`, Thanks to [@aboks]
- [#1674] Add Polyfil function support
- [#1681] Add `parent::__construct()` to command dependencies

### Changed

- [#1748] [CodingStyle] Improve `NewlineBeforeNewAssignSetRector`
- [#1697] [DeadCode] Allow static constant call on `RemoveUnusedPrivateConstantRector`, Thanks to [@ravanscafi]
- [#1719] Resolve anonymous class return type to object

### Fixed

- [#1752] [CodeQuality] Fix `CompleteDynamicPropertiesRector` for dynamic property fetch
- [#1718] [DeadCode] Fix too deep nesting in dead private property
- [#1710] [MethodCall] Fix multilevel array subsets, Thanks to [@ravanscafi]
- [#1715] [SOLID] Fix `PrivatizeLocalClassConstantRector` for in-class use
- [#1698] Fix `NameTypeResolver` resolveFullyQualifiedName return type, Thanks to [@ravanscafi]
- [#1684] fix new phpstan reports
- [#1702] Fixed some issues for `RemoveZeroAndOneBinaryRector`, Thanks to [@jeroensmit]
- [#1703] Fixed unintended removal of properties when used inside a trait, Thanks to [@jeroensmit]
- [#1738] Fix InjectAnnotationClassRector with aliases
- [#1705] Fixed wrong naming of docs script in composer.json, Thanks to [@jeroensmit]
- [#1712] Fix tests according to review and a few typos, Thanks to [@ravanscafi]
- [#1673] Fix `InjectAnnotationClassRector` for `@var` case
- [#1677] [Bugfix] `IsCountableRector` & `IsIterableRector` should first check method availability, Thanks to [@stloyd]
- [#1686] [Bugfix] PHPDoc type-hint `resource` should not be used as PHP type-hint, Thanks to [@stloyd]
- [#1739] [CodeStyle] Newline before assign
- [#1716] [DeadCode] Keep array method call in `RemoveUnusedPrivateMethodRector`
- [#1753] [DeadCode] Rector `RemoveDeadConstructorRector` should skip `private` method, Thanks to [@stloyd]
- [#1687] [Symfony] Set few default common service names for Symfony App Analyzer, Thanks to [@stloyd]
- [#1675] [Symfony] Make set symfony42 refactor get(...) in former container aware commands
- [#1666] Skip session in `SelfContainerGetMethodCallFromTestToSetUpMethodRector`
- [#1757] make SymfonyContainer factory configurable with "kernel_environment" parameter in rector.yaml
- [#1707] Don't mess with lines between docblock comment and var type., Thanks to [@ravanscafi]
- [#1699] Update composer scripts, Thanks to [@ravanscafi]
- [#1755] make interface description PHPStorm compatible, so it will not break abstract method complete
- [#1711] Do not mark injected properties as private when moved to constructor, Thanks to [@holtkamp]
- [#1714] Cleanup
- [#1721] skip `Illuminate\Support\Collection` magic for `CompleteDynamicPropertiesRector`
- [#1725] Empty compacts are forbidden, keep signature by replacing with empty array, Thanks to [@ravanscafi]
- [#1728] `is_real()` is deprecated instead of `is_float()`, Thanks to [@holtkamp]
- [#1735] Consider reference symbol in docblock for param type declaration rector, Thanks to [@tigitz]
- [#1736] Colorify neon files, Thanks to [@szepeviktor]
- [#1737] Typo in Travis config, Thanks to [@szepeviktor]

### Removed

- [#1679] [MakeCommandLazyRector] Remove duplicated check, Thanks to [@stloyd]
- [#1701] Make sure parameter is not removed when a child class does use the parameter, Thanks to [@jeroensmit]
- [#1723] Do not remove args when replacing to static calls, Thanks to [@ravanscafi]
- [#1713] Remove `--with-style` in favour of mentioning ECS, Thanks to [@stloyd]

## [v0.5.7] - 2019-06-28

### Fixed

- [#1661] Minor phpdoc fixes

## [v0.5.6] - 2019-06-28

### Removed

- [#1659] remove deprecated singly implemented autowire compiler pass

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.5.6] - 2019-06-28

### Added

- [#1584] [DeadCode] Add `RemoveDeadZeroAndOneOperationRector`
- [#1586] [DeadCode] Add `RemoveDelegatingParentCallRector`
- [#1603] [DeadCode] Add `RemoveDuplicatedInstanceOfRector`
- [#1656] [SymfonyPHPUnit] Add `SelfContainerGetMethodCallFromTestToSetUpMethodRector`
- [#1589] Add assign ref support to `AddDefaultValueForUndefinedVariableRector`
- [#1609] Add `ElasticSearchDSL` package, Thanks to [@shyim]
- [#1611] Add rector for ShopRegistration, Thanks to [@shyim]
- [#1615] add exclude to typical reported typos
- [#1610] Add shopware version const rector, Thanks to [@shyim]
- [#1640] Add `--rule` option to process only single rule from set

### Changed

- [#1582] Rename "level" directory to "set"
- [#1612] travis: allow PHP 7.4

### Fixed

- [#1619] [CodeQuality] Fix `__set`/`__get` case for `CompleteDynamicPropertiesRector`
- [#1643] [CodingStyle] Fix extra slash in array simple types
- [#1616] [DeadCode] Fix removed comment after return at `RemoveCodeAfterReturnRector`
- [#1602] [Laravel] Fix missing method name in 5.7
- [#1645] [PHP] Fix `mktime` rename with args [closes [#1622]]
- [#1647] [PHP] Fix `JsonThrowOnErrorRector` inter-args
- [#1644] [PHP] Fix missed variadic on `ReflectionMethod::invoke()` [closes [#1625]]
- [#1618] [PHP] Fix class signature over interface priority in `RemoveExtraParametersRector`
- [#1642] [PHP] Fix `StringifyStrNeedlesRector` duplicated change
- [#1617] [Symfony] Fix GetRequestRector overlap to non-controllers
- [#1605] Fix Open Collective link for `FUNDING.yml`, Thanks to [@pxgamer]
- [#1583] Fix CountOnNullRector for nullable and invalid property
- [#1599] Fix `StringClassNameToClassConstantRector` for empty name [closes [#1596]]
- [#1590] Fix nullable item in `ListSwapArrayOrderRector`
- [#1631] Fix typo : rename `jsm-decouple.yaml` to `jms-decouple.yaml`, Thanks to [@gnutix]
- [#1588] Fix foreach scope for `AddDefaultValueForUndefinedVariableRector`
- [#1601] Fix trait skip in `RemoveParentCallWithoutParentRector`

### Changed

- [#1587] [PHP] Skip list in `AddDefaultValueForUndefinedVariableRector`
- [#1651] Update link to `UPGRADE.md 3.0`, Thanks to [@vasilvestre]
- [#1581] use `STOP_TRAVERSAL` over exception
- [#1525] [#1469] prototype github issue template, Thanks to [@funivan]

## [v0.5.5] - 2019-06-08

- [#1577] skip analysis of new anonymous classes in method call [closes [#1574]]

<!-- dumped content end -->

## [v0.5.4] - 2019-06-06

### Added

- [#1570] [DeadCode] Add `RemoveConcatAutocastRector`
- [#1519] [Symfony] Add `MakeCommandLazyRector`
- [#1568] [Symfony 4.3] Add `parent::__construct` to `EventDispatcher`
- [#1562] add `CallableNodeTraverserTrait`

### Changed

- [#1523] make RectorsFinder return consistent order by shorter names
- [#1572] [Symfony 4.3] Improve event name and class flip
- [#1548] Widen `PHPStan` version constraint to `~0.11.6`., Thanks to [@Aerendir]

### Fixed

- [#1550] Fix `symfony/finder` 3.4 compact in `LevelOptionResolver`
- [#1544] Fix phpdoc-parser BC break for generic multiline nodes
- [#1569] Fix reporting of changed nodes
- [#1559] Fix classname change for `FilterControllerEvent`, Thanks to [@keulinho]
- [#1557] Fix scope overflow in `AddDefaultValueForUndefinedVariableRector`
- [#1556] fix static method in reflection for `StaticCallOnNonStaticToInstanceCallRector`
- [#1571] Fix anonymous class method return type resolving
- [#1567] Fix `solid.yaml`, Thanks to [@Great-Antique]
- [#1549] fix unescaped regular
- [#1538] Don't remove aliases of classes with same name but different namespaces., Thanks to [@Aerendir]
- [#1553] [CodeQuality] Skip collections `ForeachToInArrayRector` [closes [#1533]]
- [#1524] Cover multiline in description-aware nodes [closes [#1522]]
- [#1565] make `StringClassNameToClassConstantRector` case sensitive [closes [#1539]]
- [#1545] Ensure Doctrine's `Collection`-like arrays are ignored., Thanks to [@Aerendir]
- [#1554] optimize
- [#1558] Do not call parent constructor of `AutowiredEventDispatcher` unless it exists, Thanks to [@cgkkevinr]
- [#1561] make `RemoveUnusedAliasRector` take into account aliases that keep 2 classes with same short name explicit
- [#1555] skip nullable array for `ArrayPropertyDefaultValueRector` [closes [#1542]]

## [v0.5.3] - 2019-06-01

### Added

- [#1520] [PHP] Add `is_countable` to `CountOnNull`

### Changed

- [#1521] make `LevelOptionResolver` smarter + add `--set` alias to `--level`

## [v0.5.2] - 2019-05-31

### Fixed

- [#1510] [CodeQuality] Add trait and parent class support for `CompleteDynamicPropertiesRector`
- [#1508] [CodeQuality] Fix unneeded return in `CallableThisArrayToAnonymousFunctionRector`
- [#1509] [PHP] Fix `AddDefaultValueForUndefinedVariableRector` for static variable
- [#1507] [PHP] Fix `BinaryOpBetweenNumberAndStringRector` for concat
- [#1517] fix `RenameClassRector` to change only direct class names, not children
- [#1511] fix `PHPStormVarAnnotationRector` for too nested var [closes [#1407]]
- [#1513] Make Symfony 4.3 + phpdoc-parser 0.3.4 compatible
- [#1506] Ensure `static` variables are considered as declared, Thanks to [@Aerendir]
- [#1502] Test concatenation dot is ignored, Thanks to [@Aerendir]

## [v0.5.1] - 2019-05-30

### Added

- [#1496] [Symfony 4.3] Add class renames, method renames and added arguments

### Fixed

- [#1493] Fix documentation minor mistake, Thanks to [@alterphp]

<!-- dumped content end -->

## [v0.5.0] - 2019-05-28

### Added

- [#1487] [Legacy] Remove singleton
- [#1468] [MultipleClassFileToPsr4ClassesRector] Original file is deleted even if class matches filename, Thanks to [@JanMikes]
- [#1424] [SplitStringClassConstantToClassConstFetchRector] Remove duplicated namespace separator, Thanks to [@mxr576]
- [#1470] [PHP][CodingStyle] string class to ::class
- [#1367] [DeadCode] Anonymous class implementing an interface doesn't respect interface signature, Thanks to [@pierredup]
- [#1404] [TypeDeclaration] Create new set
- [#1414] [PSR-4 Split] Split interfaces and traits as well, Thanks to [@JanMikes]
- [#1416] [PHP 7.4] Spread array
- [#1419] [CodeQuality] Add For to foreach
- [#1443] [CodeQuality] Add CompactToVariablesRector
- [#1488] 🎉[CodeQuality] Add CompleteDynamicPropertiesRector
- [#1390] [CodeQuality] Add AndAssignsToSeparateLinesRector
- [#1485] [CodingStyle] Add VarConstantCommentRector
- [#1484] [CodingStyle] Add SplitDoubleAssignRector
- [#1483] [CodingStyle] Add ArrayPropertyDefaultValueRector
- [#1482] [CodingStyle] Add CatchExceptionNameMatchingTypeRector
- [#1481] [CodingStyle] Add FollowRequireByDirRector
- [#1480] [CodingStyle] Add ConsistentPregDelimiterRector
- [#1447] [CodingStyle] add partial support already imported support to ImportFullyQualifiedNamesRector
- [#1389] [DeadCode] Add RemoveAndTrueRector
- [#1392] [DeadCode] Add RemoveDefaultArgumentValueRector
- [#1451] [PHP] Add RemoveMissingCompactVariableRector
- [#1418] [PHP-DI] Add php-di [@Inject] annotation import
- [#1460] [Psr4] Add supprot for namespace less MultipleClassFileToPsr4ClassesRector
- [#1486] [SOLID] Add AbstractChildlessUnusedClassesRector
- [#1406] [TypeDeclaration] Add AddFunctionReturnTypeRector
- [#1403] [Symfony 4.3] Add swapped dispatch() arguments for EventDispatcher
- [#1429] Add missing end bracket at HowItWorks.md's sample, Thanks to [@sasezaki]
- [#1430] Add working directory option, Thanks to [@ktomk]
- [#1417] Add trait analysis without class dependency
- [#1491] add JsonOutputFormatter
- [#1492] Symplify 6 bump + add relative paths to JsonOutputFormatter
- [#1410] [PHP 7.4] Add ClosureToArrowFunctionRector
- [#1449] [PHP 7.1] Add BinaryOpBetweenNumberAndStringRector
- [#1450] [PHP 7.1] Add float to BinaryOpBetweenNumberAndStringRector
- [#1452] Add non-namespaced support to ImportFullyQualifiedNamesRector
- [#1461] [supporŧ] add funding Github - news from Github Satellite
- [#1478] composer: add authors
- [#1382] Add support to rename classes and it's namespace, Thanks to [@JanMikes]
- [#1377] Add function support to ImportFullyQualifiedNamesRector
- [#1489] [backers] add Jan Votruba

### Changed

- [#1412] [ImportFullyQualifiedNamesRector] Allow to opt-out from doc block modification, Thanks to [@mxr576]
- [#1439] [PSR4] Improve MultipleClassFileToPsr4ClassesRector output
- [#1477] introduce OutputFormatterCollector to allow extension of output formatters
- [#1446] Narrow ArraySpreadInsteadOfArrayMergeRector to numeric-arrays only
- [#1479] update to php-parser 4.2.2

### Fixed

- [#1395] Preserve file permissions when updating a file, Thanks to [@LeSuisse]
- [#1397] [DeadCode] Various set fixes
- [#1398] Fix travis for Laravel self-run
- [#1391] fix ImportFullyQualifiedNamesRector on multiple files
- [#1444] fix ReservedObjectRector for lowercased object
- [#1471] fix deleting file that matches class name
- [#1425] speedup RenameClassRector on doc comments
- [#1464] do not override printing files with previous stmts if possible

### Removed

- [#1415] Removed duplicated code, Thanks to [@DaveLiddament]

## [v0.4.12] - 2019-05-02

### Added

- [#1326] [CodingStyle] Add SplitStringClassConstantToClassConstFetchRector
- [#1327] [CodingStyle] Add ImportFullyQualifiedNamesRector
- [#1363] [PHP] Add AddDefaultValueForUndefinedVariableRector
- [#1347] [RemovingStatic] Add new level
- [#1333] Add PrivatizeLocalClassConstantRector to SOLID, Thanks to [@mxr576]
- [#1362] [PHP 7.4] Add ReservedFnFunctionRector

### Changed

- [#1323] allow Nette 3.0, Thanks to [@mimmi20]
- [#1325] [DeadCode] Skip magic methods in RemoveUnusedParameterRector
- [#1351] [DeadCode] Keep different case in RemoveDoubleAssignRector
- [#1353] [DeadCode] Skip traits in RemoveUnusedPrivateMethodRector
- [#1370] make ImportFullyQualifiedNamesRector take into account existing imports on combination of PHP and doc block
- [#1354] Speedup tests by 90 % from 41 secs to 4
- [#1357] Tests improvements
- [#1359] Notice file rectors on run

### Fixed

- [#1369] [CodingStyle] ImportsInClassCollection fixes
- [#1368] [CodingStyle] Fix ImportFullyQualifiedNamesRector for self imports
- [#1365] [CodingStyle] Fix interface short name identical with class name in ImportFullyQualifiedNamesRector
- [#1348] [DeadCode] Remove overriden fix
- [#1352] [DeadCode] Fix RemoveUnusedPrivateMethodRector for self call
- [#1350] [Laravel] Fix MinutesToSecondsInCacheRector DateTimeInterface argument
- [#1361] [Symfony] Fix GetRequestRector for get non method calls
- [#1375] Fix file removal in MultipleClassFileToPsr4ClassesRector
- [#1320] [CakePHP]FIx rule for cakephp37, Thanks to [@o0h]
- [#1331] Use `dev` as Symfony default env to fix issue [#1319], Thanks to [@BernhardWebstudio]

### Removed

- [#1349] [DeadCode] Remove double

## [v0.4.11] - 2019-04-14

### Added

- [#1317] Add Changelog
- [#1302] [Symfony 4.3] Add SimplifyWebTestCaseAssertionsRector
- [#1302] [Symfony 4.3] Add `SimplifyWebTestCaseAssertionsRector`
- [#1311] [CodingStyle] Add `SplitGroupedConstantsAndPropertiesRector`
- [#1301] [PHPUnit] Add `RemoveExpectAnyFromMockRector`
- [#1304] [SOLID] Add `PrivatizeLocalClassConstantRector`
- [#1303] [SOLID] Add `FinalizeClassesWithoutChildrenRector`
- [#1302] [Symfony 4.3] Add `SimplifyWebTestCaseAssertionsRector`

### Changed

- [#1316] Merge collected nodes to ParsedNodesByType
- [#1314] rename `Attribute` to `AttributeKey` to prevent duplicated names with other projects
- [#1318] Update reference to drupal8-rector/drupal8-rector, Thanks to [@mxr576]
- [#1316] Merge collected nodes to ParsedNodesByType

### Fixed

- [#1305] [Symfony 3.0] Fix wrong indentation in symfony30.yaml, Thanks to [@Dodenis]

[#1302]: https://github.com/rectorphp/rector/pull/1302
[#1314]: https://github.com/rectorphp/rector/pull/1314
[#1311]: https://github.com/rectorphp/rector/pull/1311
[#1305]: https://github.com/rectorphp/rector/pull/1305
[#1304]: https://github.com/rectorphp/rector/pull/1304
[#1303]: https://github.com/rectorphp/rector/pull/1303
[#1301]: https://github.com/rectorphp/rector/pull/1301
[@Dodenis]: https://github.com/Dodenis
[#1318]: https://github.com/rectorphp/rector/pull/1318
[#1317]: https://github.com/rectorphp/rector/pull/1317
[#1316]: https://github.com/rectorphp/rector/pull/1316
[@mxr576]: https://github.com/mxr576
[#1375]: https://github.com/rectorphp/rector/pull/1375
[#1370]: https://github.com/rectorphp/rector/pull/1370
[#1369]: https://github.com/rectorphp/rector/pull/1369
[#1368]: https://github.com/rectorphp/rector/pull/1368
[#1365]: https://github.com/rectorphp/rector/pull/1365
[#1363]: https://github.com/rectorphp/rector/pull/1363
[#1362]: https://github.com/rectorphp/rector/pull/1362
[#1361]: https://github.com/rectorphp/rector/pull/1361
[#1359]: https://github.com/rectorphp/rector/pull/1359
[#1357]: https://github.com/rectorphp/rector/pull/1357
[#1354]: https://github.com/rectorphp/rector/pull/1354
[#1353]: https://github.com/rectorphp/rector/pull/1353
[#1352]: https://github.com/rectorphp/rector/pull/1352
[#1351]: https://github.com/rectorphp/rector/pull/1351
[#1350]: https://github.com/rectorphp/rector/pull/1350
[#1349]: https://github.com/rectorphp/rector/pull/1349
[#1348]: https://github.com/rectorphp/rector/pull/1348
[#1347]: https://github.com/rectorphp/rector/pull/1347
[#1333]: https://github.com/rectorphp/rector/pull/1333
[#1331]: https://github.com/rectorphp/rector/pull/1331
[#1327]: https://github.com/rectorphp/rector/pull/1327
[#1326]: https://github.com/rectorphp/rector/pull/1326
[#1325]: https://github.com/rectorphp/rector/pull/1325
[#1323]: https://github.com/rectorphp/rector/pull/1323
[#1320]: https://github.com/rectorphp/rector/pull/1320
[#1319]: https://github.com/rectorphp/rector/pull/1319
[@o0h]: https://github.com/o0h
[@mimmi20]: https://github.com/mimmi20
[@BernhardWebstudio]: https://github.com/BernhardWebstudio
[v0.4.11]: https://github.com/rectorphp/rector/compare/v0.4.11...v0.4.11

[#1492]: https://github.com/rectorphp/rector/pull/1492
[#1491]: https://github.com/rectorphp/rector/pull/1491
[#1489]: https://github.com/rectorphp/rector/pull/1489
[#1488]: https://github.com/rectorphp/rector/pull/1488
[#1487]: https://github.com/rectorphp/rector/pull/1487
[#1486]: https://github.com/rectorphp/rector/pull/1486
[#1485]: https://github.com/rectorphp/rector/pull/1485
[#1484]: https://github.com/rectorphp/rector/pull/1484
[#1483]: https://github.com/rectorphp/rector/pull/1483
[#1482]: https://github.com/rectorphp/rector/pull/1482
[#1481]: https://github.com/rectorphp/rector/pull/1481
[#1480]: https://github.com/rectorphp/rector/pull/1480
[#1479]: https://github.com/rectorphp/rector/pull/1479
[#1478]: https://github.com/rectorphp/rector/pull/1478
[#1477]: https://github.com/rectorphp/rector/pull/1477
[#1471]: https://github.com/rectorphp/rector/pull/1471
[#1470]: https://github.com/rectorphp/rector/pull/1470
[#1468]: https://github.com/rectorphp/rector/pull/1468
[#1464]: https://github.com/rectorphp/rector/pull/1464
[#1461]: https://github.com/rectorphp/rector/pull/1461
[#1460]: https://github.com/rectorphp/rector/pull/1460
[#1452]: https://github.com/rectorphp/rector/pull/1452
[#1451]: https://github.com/rectorphp/rector/pull/1451
[#1450]: https://github.com/rectorphp/rector/pull/1450
[#1449]: https://github.com/rectorphp/rector/pull/1449
[#1447]: https://github.com/rectorphp/rector/pull/1447
[#1446]: https://github.com/rectorphp/rector/pull/1446
[#1444]: https://github.com/rectorphp/rector/pull/1444
[#1443]: https://github.com/rectorphp/rector/pull/1443
[#1439]: https://github.com/rectorphp/rector/pull/1439
[#1430]: https://github.com/rectorphp/rector/pull/1430
[#1429]: https://github.com/rectorphp/rector/pull/1429
[#1425]: https://github.com/rectorphp/rector/pull/1425
[#1424]: https://github.com/rectorphp/rector/pull/1424
[#1419]: https://github.com/rectorphp/rector/pull/1419
[#1418]: https://github.com/rectorphp/rector/pull/1418
[#1417]: https://github.com/rectorphp/rector/pull/1417
[#1416]: https://github.com/rectorphp/rector/pull/1416
[#1415]: https://github.com/rectorphp/rector/pull/1415
[#1414]: https://github.com/rectorphp/rector/pull/1414
[#1412]: https://github.com/rectorphp/rector/pull/1412
[#1410]: https://github.com/rectorphp/rector/pull/1410
[#1406]: https://github.com/rectorphp/rector/pull/1406
[#1404]: https://github.com/rectorphp/rector/pull/1404
[#1403]: https://github.com/rectorphp/rector/pull/1403
[#1398]: https://github.com/rectorphp/rector/pull/1398
[#1397]: https://github.com/rectorphp/rector/pull/1397
[#1395]: https://github.com/rectorphp/rector/pull/1395
[#1392]: https://github.com/rectorphp/rector/pull/1392
[#1391]: https://github.com/rectorphp/rector/pull/1391
[#1390]: https://github.com/rectorphp/rector/pull/1390
[#1389]: https://github.com/rectorphp/rector/pull/1389
[#1382]: https://github.com/rectorphp/rector/pull/1382
[#1377]: https://github.com/rectorphp/rector/pull/1377
[#1367]: https://github.com/rectorphp/rector/pull/1367
[@sasezaki]: https://github.com/sasezaki
[@pierredup]: https://github.com/pierredup
[@ktomk]: https://github.com/ktomk
[@LeSuisse]: https://github.com/LeSuisse
[@JanMikes]: https://github.com/JanMikes
[@Inject]: https://github.com/Inject
[@DaveLiddament]: https://github.com/DaveLiddament
[v0.4.12]: https://github.com/rectorphp/rector/compare/v0.4.11...v0.4.12
[v0.5.0]: https://github.com/rectorphp/rector/compare/v0.4.12...v0.5.0
[#1521]: https://github.com/rectorphp/rector/pull/1521
[#1520]: https://github.com/rectorphp/rector/pull/1520
[#1517]: https://github.com/rectorphp/rector/pull/1517
[#1513]: https://github.com/rectorphp/rector/pull/1513
[#1511]: https://github.com/rectorphp/rector/pull/1511
[#1510]: https://github.com/rectorphp/rector/pull/1510
[#1509]: https://github.com/rectorphp/rector/pull/1509
[#1508]: https://github.com/rectorphp/rector/pull/1508
[#1507]: https://github.com/rectorphp/rector/pull/1507
[#1506]: https://github.com/rectorphp/rector/pull/1506
[#1502]: https://github.com/rectorphp/rector/pull/1502
[#1496]: https://github.com/rectorphp/rector/pull/1496
[#1493]: https://github.com/rectorphp/rector/pull/1493
[#1407]: https://github.com/rectorphp/rector/pull/1407
[v0.5.2]: https://github.com/rectorphp/rector/compare/v0.5.1...v0.5.2
[@alterphp]: https://github.com/alterphp
[@Aerendir]: https://github.com/Aerendir
[v0.5.1]: https://github.com/rectorphp/rector/compare/v0.5.0...v0.5.1
[#1572]: https://github.com/rectorphp/rector/pull/1572
[#1571]: https://github.com/rectorphp/rector/pull/1571
[#1570]: https://github.com/rectorphp/rector/pull/1570
[#1569]: https://github.com/rectorphp/rector/pull/1569
[#1568]: https://github.com/rectorphp/rector/pull/1568
[#1567]: https://github.com/rectorphp/rector/pull/1567
[#1565]: https://github.com/rectorphp/rector/pull/1565
[#1562]: https://github.com/rectorphp/rector/pull/1562
[#1561]: https://github.com/rectorphp/rector/pull/1561
[#1559]: https://github.com/rectorphp/rector/pull/1559
[#1558]: https://github.com/rectorphp/rector/pull/1558
[#1557]: https://github.com/rectorphp/rector/pull/1557
[#1556]: https://github.com/rectorphp/rector/pull/1556
[#1555]: https://github.com/rectorphp/rector/pull/1555
[#1554]: https://github.com/rectorphp/rector/pull/1554
[#1553]: https://github.com/rectorphp/rector/pull/1553
[#1550]: https://github.com/rectorphp/rector/pull/1550
[#1549]: https://github.com/rectorphp/rector/pull/1549
[#1548]: https://github.com/rectorphp/rector/pull/1548
[#1545]: https://github.com/rectorphp/rector/pull/1545
[#1544]: https://github.com/rectorphp/rector/pull/1544
[#1542]: https://github.com/rectorphp/rector/pull/1542
[#1539]: https://github.com/rectorphp/rector/pull/1539
[#1538]: https://github.com/rectorphp/rector/pull/1538
[#1533]: https://github.com/rectorphp/rector/pull/1533
[#1524]: https://github.com/rectorphp/rector/pull/1524
[#1523]: https://github.com/rectorphp/rector/pull/1523
[#1522]: https://github.com/rectorphp/rector/pull/1522
[#1519]: https://github.com/rectorphp/rector/pull/1519
[@keulinho]: https://github.com/keulinho
[@cgkkevinr]: https://github.com/cgkkevinr
[@Great-Antique]: https://github.com/Great-Antique
[v0.5.3]: https://github.com/rectorphp/rector/compare/v0.5.2...v0.5.3
[#1659]: https://github.com/rectorphp/rector/pull/1659
[#1656]: https://github.com/rectorphp/rector/pull/1656
[#1651]: https://github.com/rectorphp/rector/pull/1651
[#1647]: https://github.com/rectorphp/rector/pull/1647
[#1645]: https://github.com/rectorphp/rector/pull/1645
[#1644]: https://github.com/rectorphp/rector/pull/1644
[#1643]: https://github.com/rectorphp/rector/pull/1643
[#1642]: https://github.com/rectorphp/rector/pull/1642
[#1640]: https://github.com/rectorphp/rector/pull/1640
[#1631]: https://github.com/rectorphp/rector/pull/1631
[#1625]: https://github.com/rectorphp/rector/pull/1625
[#1622]: https://github.com/rectorphp/rector/pull/1622
[#1619]: https://github.com/rectorphp/rector/pull/1619
[#1618]: https://github.com/rectorphp/rector/pull/1618
[#1617]: https://github.com/rectorphp/rector/pull/1617
[#1616]: https://github.com/rectorphp/rector/pull/1616
[#1615]: https://github.com/rectorphp/rector/pull/1615
[#1612]: https://github.com/rectorphp/rector/pull/1612
[#1611]: https://github.com/rectorphp/rector/pull/1611
[#1610]: https://github.com/rectorphp/rector/pull/1610
[#1609]: https://github.com/rectorphp/rector/pull/1609
[#1605]: https://github.com/rectorphp/rector/pull/1605
[#1603]: https://github.com/rectorphp/rector/pull/1603
[#1602]: https://github.com/rectorphp/rector/pull/1602
[#1601]: https://github.com/rectorphp/rector/pull/1601
[#1599]: https://github.com/rectorphp/rector/pull/1599
[#1596]: https://github.com/rectorphp/rector/pull/1596
[#1590]: https://github.com/rectorphp/rector/pull/1590
[#1589]: https://github.com/rectorphp/rector/pull/1589
[#1588]: https://github.com/rectorphp/rector/pull/1588
[#1587]: https://github.com/rectorphp/rector/pull/1587
[#1586]: https://github.com/rectorphp/rector/pull/1586
[#1584]: https://github.com/rectorphp/rector/pull/1584
[#1583]: https://github.com/rectorphp/rector/pull/1583
[#1582]: https://github.com/rectorphp/rector/pull/1582
[#1581]: https://github.com/rectorphp/rector/pull/1581
[#1577]: https://github.com/rectorphp/rector/pull/1577
[#1574]: https://github.com/rectorphp/rector/pull/1574
[#1525]: https://github.com/rectorphp/rector/pull/1525
[#1469]: https://github.com/rectorphp/rector/pull/1469
[@vasilvestre]: https://github.com/vasilvestre
[@shyim]: https://github.com/shyim
[@pxgamer]: https://github.com/pxgamer
[@gnutix]: https://github.com/gnutix
[@funivan]: https://github.com/funivan
[v0.5.5]: https://github.com/rectorphp/rector/compare/v0.5.4...v0.5.5
[v0.5.4]: https://github.com/rectorphp/rector/compare/v0.5.3...v0.5.4
[#1757]: https://github.com/rectorphp/rector/pull/1757
[#1755]: https://github.com/rectorphp/rector/pull/1755
[#1754]: https://github.com/rectorphp/rector/pull/1754
[#1753]: https://github.com/rectorphp/rector/pull/1753
[#1752]: https://github.com/rectorphp/rector/pull/1752
[#1748]: https://github.com/rectorphp/rector/pull/1748
[#1744]: https://github.com/rectorphp/rector/pull/1744
[#1739]: https://github.com/rectorphp/rector/pull/1739
[#1738]: https://github.com/rectorphp/rector/pull/1738
[#1737]: https://github.com/rectorphp/rector/pull/1737
[#1736]: https://github.com/rectorphp/rector/pull/1736
[#1735]: https://github.com/rectorphp/rector/pull/1735
[#1728]: https://github.com/rectorphp/rector/pull/1728
[#1725]: https://github.com/rectorphp/rector/pull/1725
[#1723]: https://github.com/rectorphp/rector/pull/1723
[#1722]: https://github.com/rectorphp/rector/pull/1722
[#1721]: https://github.com/rectorphp/rector/pull/1721
[#1720]: https://github.com/rectorphp/rector/pull/1720
[#1719]: https://github.com/rectorphp/rector/pull/1719
[#1718]: https://github.com/rectorphp/rector/pull/1718
[#1717]: https://github.com/rectorphp/rector/pull/1717
[#1716]: https://github.com/rectorphp/rector/pull/1716
[#1715]: https://github.com/rectorphp/rector/pull/1715
[#1714]: https://github.com/rectorphp/rector/pull/1714
[#1713]: https://github.com/rectorphp/rector/pull/1713
[#1712]: https://github.com/rectorphp/rector/pull/1712
[#1711]: https://github.com/rectorphp/rector/pull/1711
[#1710]: https://github.com/rectorphp/rector/pull/1710
[#1709]: https://github.com/rectorphp/rector/pull/1709
[#1707]: https://github.com/rectorphp/rector/pull/1707
[#1705]: https://github.com/rectorphp/rector/pull/1705
[#1703]: https://github.com/rectorphp/rector/pull/1703
[#1702]: https://github.com/rectorphp/rector/pull/1702
[#1701]: https://github.com/rectorphp/rector/pull/1701
[#1699]: https://github.com/rectorphp/rector/pull/1699
[#1698]: https://github.com/rectorphp/rector/pull/1698
[#1697]: https://github.com/rectorphp/rector/pull/1697
[#1696]: https://github.com/rectorphp/rector/pull/1696
[#1695]: https://github.com/rectorphp/rector/pull/1695
[#1694]: https://github.com/rectorphp/rector/pull/1694
[#1693]: https://github.com/rectorphp/rector/pull/1693
[#1691]: https://github.com/rectorphp/rector/pull/1691
[#1690]: https://github.com/rectorphp/rector/pull/1690
[#1689]: https://github.com/rectorphp/rector/pull/1689
[#1687]: https://github.com/rectorphp/rector/pull/1687
[#1686]: https://github.com/rectorphp/rector/pull/1686
[#1684]: https://github.com/rectorphp/rector/pull/1684
[#1681]: https://github.com/rectorphp/rector/pull/1681
[#1679]: https://github.com/rectorphp/rector/pull/1679
[#1677]: https://github.com/rectorphp/rector/pull/1677
[#1676]: https://github.com/rectorphp/rector/pull/1676
[#1675]: https://github.com/rectorphp/rector/pull/1675
[#1674]: https://github.com/rectorphp/rector/pull/1674
[#1673]: https://github.com/rectorphp/rector/pull/1673
[#1671]: https://github.com/rectorphp/rector/pull/1671
[#1666]: https://github.com/rectorphp/rector/pull/1666
[#1661]: https://github.com/rectorphp/rector/pull/1661
[v0.5.8]: https://github.com/rectorphp/rector/compare/v0.5.7...v0.5.8
[v0.5.7]: https://github.com/rectorphp/rector/compare/v0.5.6...v0.5.7
[@tigitz]: https://github.com/tigitz
[@szepeviktor]: https://github.com/szepeviktor
[@stloyd]: https://github.com/stloyd
[@ravanscafi]: https://github.com/ravanscafi
[@jeroensmit]: https://github.com/jeroensmit
[@holtkamp]: https://github.com/holtkamp
[@aboks]: https://github.com/aboks
[v0.5.6]: https://github.com/rectorphp/rector/compare/v0.5.5...v0.5.6
[#1864]: https://github.com/rectorphp/rector/pull/1864
[#1863]: https://github.com/rectorphp/rector/pull/1863
[#1862]: https://github.com/rectorphp/rector/pull/1862
[#1859]: https://github.com/rectorphp/rector/pull/1859
[#1858]: https://github.com/rectorphp/rector/pull/1858
[#1857]: https://github.com/rectorphp/rector/pull/1857
[#1856]: https://github.com/rectorphp/rector/pull/1856
[#1855]: https://github.com/rectorphp/rector/pull/1855
[#1854]: https://github.com/rectorphp/rector/pull/1854
[#1853]: https://github.com/rectorphp/rector/pull/1853
[#1852]: https://github.com/rectorphp/rector/pull/1852
[#1851]: https://github.com/rectorphp/rector/pull/1851
[#1850]: https://github.com/rectorphp/rector/pull/1850
[#1845]: https://github.com/rectorphp/rector/pull/1845
[#1844]: https://github.com/rectorphp/rector/pull/1844
[#1842]: https://github.com/rectorphp/rector/pull/1842
[#1841]: https://github.com/rectorphp/rector/pull/1841
[#1840]: https://github.com/rectorphp/rector/pull/1840
[#1839]: https://github.com/rectorphp/rector/pull/1839
[#1838]: https://github.com/rectorphp/rector/pull/1838
[#1837]: https://github.com/rectorphp/rector/pull/1837
[#1836]: https://github.com/rectorphp/rector/pull/1836
[#1835]: https://github.com/rectorphp/rector/pull/1835
[#1833]: https://github.com/rectorphp/rector/pull/1833
[#1832]: https://github.com/rectorphp/rector/pull/1832
[#1831]: https://github.com/rectorphp/rector/pull/1831
[#1830]: https://github.com/rectorphp/rector/pull/1830
[#1829]: https://github.com/rectorphp/rector/pull/1829
[#1828]: https://github.com/rectorphp/rector/pull/1828
[#1827]: https://github.com/rectorphp/rector/pull/1827
[#1826]: https://github.com/rectorphp/rector/pull/1826
[#1825]: https://github.com/rectorphp/rector/pull/1825
[#1823]: https://github.com/rectorphp/rector/pull/1823
[#1821]: https://github.com/rectorphp/rector/pull/1821
[#1819]: https://github.com/rectorphp/rector/pull/1819
[#1818]: https://github.com/rectorphp/rector/pull/1818
[#1817]: https://github.com/rectorphp/rector/pull/1817
[#1814]: https://github.com/rectorphp/rector/pull/1814
[#1813]: https://github.com/rectorphp/rector/pull/1813
[#1808]: https://github.com/rectorphp/rector/pull/1808
[#1807]: https://github.com/rectorphp/rector/pull/1807
[#1806]: https://github.com/rectorphp/rector/pull/1806
[#1805]: https://github.com/rectorphp/rector/pull/1805
[#1804]: https://github.com/rectorphp/rector/pull/1804
[#1803]: https://github.com/rectorphp/rector/pull/1803
[#1802]: https://github.com/rectorphp/rector/pull/1802
[#1800]: https://github.com/rectorphp/rector/pull/1800
[#1794]: https://github.com/rectorphp/rector/pull/1794
[#1793]: https://github.com/rectorphp/rector/pull/1793
[#1791]: https://github.com/rectorphp/rector/pull/1791
[#1790]: https://github.com/rectorphp/rector/pull/1790
[#1789]: https://github.com/rectorphp/rector/pull/1789
[#1788]: https://github.com/rectorphp/rector/pull/1788
[#1787]: https://github.com/rectorphp/rector/pull/1787
[#1786]: https://github.com/rectorphp/rector/pull/1786
[#1782]: https://github.com/rectorphp/rector/pull/1782
[#1781]: https://github.com/rectorphp/rector/pull/1781
[#1780]: https://github.com/rectorphp/rector/pull/1780
[#1779]: https://github.com/rectorphp/rector/pull/1779
[#1777]: https://github.com/rectorphp/rector/pull/1777
[#1776]: https://github.com/rectorphp/rector/pull/1776
[#1774]: https://github.com/rectorphp/rector/pull/1774
[#1772]: https://github.com/rectorphp/rector/pull/1772
[#1771]: https://github.com/rectorphp/rector/pull/1771
[#1769]: https://github.com/rectorphp/rector/pull/1769
[#1764]: https://github.com/rectorphp/rector/pull/1764
[#1762]: https://github.com/rectorphp/rector/pull/1762
[#1761]: https://github.com/rectorphp/rector/pull/1761
[#1760]: https://github.com/rectorphp/rector/pull/1760
[#1759]: https://github.com/rectorphp/rector/pull/1759
[@snapshotpl]: https://github.com/snapshotpl
[@slepic]: https://github.com/slepic
[@scheb]: https://github.com/scheb
[@sashabeton]: https://github.com/sashabeton
[@return]: https://github.com/return
[@hernst42]: https://github.com/hernst42
[v0.5.9]: https://github.com/rectorphp/rector/compare/v0.5.8...v0.5.9
[#1895]: https://github.com/rectorphp/rector/pull/1895
[#1894]: https://github.com/rectorphp/rector/pull/1894
[#1891]: https://github.com/rectorphp/rector/pull/1891
[#1889]: https://github.com/rectorphp/rector/pull/1889
[#1888]: https://github.com/rectorphp/rector/pull/1888
[#1885]: https://github.com/rectorphp/rector/pull/1885
[#1884]: https://github.com/rectorphp/rector/pull/1884
[#1883]: https://github.com/rectorphp/rector/pull/1883
[#1882]: https://github.com/rectorphp/rector/pull/1882
[#1881]: https://github.com/rectorphp/rector/pull/1881
[#1880]: https://github.com/rectorphp/rector/pull/1880
[#1878]: https://github.com/rectorphp/rector/pull/1878
[#1875]: https://github.com/rectorphp/rector/pull/1875
[#1870]: https://github.com/rectorphp/rector/pull/1870
[#1865]: https://github.com/rectorphp/rector/pull/1865
[#1847]: https://github.com/rectorphp/rector/pull/1847
[#1815]: https://github.com/rectorphp/rector/pull/1815
[@markstory]: https://github.com/markstory
[@jacekll]: https://github.com/jacekll
[@Serializer]: https://github.com/Serializer
[@ORM]: https://github.com/ORM
[@Assert]: https://github.com/Assert
[v0.5.10]: https://github.com/rectorphp/rector/compare/v0.5.9...v0.5.10
[#2048]: https://github.com/rectorphp/rector/pull/2048
[#2046]: https://github.com/rectorphp/rector/pull/2046
[#2045]: https://github.com/rectorphp/rector/pull/2045
[#2044]: https://github.com/rectorphp/rector/pull/2044
[#2043]: https://github.com/rectorphp/rector/pull/2043
[#2040]: https://github.com/rectorphp/rector/pull/2040
[#2039]: https://github.com/rectorphp/rector/pull/2039
[#2037]: https://github.com/rectorphp/rector/pull/2037
[#2036]: https://github.com/rectorphp/rector/pull/2036
[#2035]: https://github.com/rectorphp/rector/pull/2035
[#2034]: https://github.com/rectorphp/rector/pull/2034
[#2033]: https://github.com/rectorphp/rector/pull/2033
[#2032]: https://github.com/rectorphp/rector/pull/2032
[#2031]: https://github.com/rectorphp/rector/pull/2031
[#2030]: https://github.com/rectorphp/rector/pull/2030
[#2027]: https://github.com/rectorphp/rector/pull/2027
[#2026]: https://github.com/rectorphp/rector/pull/2026
[#2025]: https://github.com/rectorphp/rector/pull/2025
[#2024]: https://github.com/rectorphp/rector/pull/2024
[#2023]: https://github.com/rectorphp/rector/pull/2023
[#2022]: https://github.com/rectorphp/rector/pull/2022
[#2021]: https://github.com/rectorphp/rector/pull/2021
[#2020]: https://github.com/rectorphp/rector/pull/2020
[#2019]: https://github.com/rectorphp/rector/pull/2019
[#2018]: https://github.com/rectorphp/rector/pull/2018
[#2017]: https://github.com/rectorphp/rector/pull/2017
[#2016]: https://github.com/rectorphp/rector/pull/2016
[#2015]: https://github.com/rectorphp/rector/pull/2015
[#2014]: https://github.com/rectorphp/rector/pull/2014
[#2013]: https://github.com/rectorphp/rector/pull/2013
[#2012]: https://github.com/rectorphp/rector/pull/2012
[#2011]: https://github.com/rectorphp/rector/pull/2011
[#2006]: https://github.com/rectorphp/rector/pull/2006
[#2003]: https://github.com/rectorphp/rector/pull/2003
[#2002]: https://github.com/rectorphp/rector/pull/2002
[#2001]: https://github.com/rectorphp/rector/pull/2001
[#2000]: https://github.com/rectorphp/rector/pull/2000
[#1999]: https://github.com/rectorphp/rector/pull/1999
[#1998]: https://github.com/rectorphp/rector/pull/1998
[#1997]: https://github.com/rectorphp/rector/pull/1997
[#1996]: https://github.com/rectorphp/rector/pull/1996
[#1994]: https://github.com/rectorphp/rector/pull/1994
[#1993]: https://github.com/rectorphp/rector/pull/1993
[#1992]: https://github.com/rectorphp/rector/pull/1992
[#1991]: https://github.com/rectorphp/rector/pull/1991
[#1990]: https://github.com/rectorphp/rector/pull/1990
[#1989]: https://github.com/rectorphp/rector/pull/1989
[#1986]: https://github.com/rectorphp/rector/pull/1986
[#1984]: https://github.com/rectorphp/rector/pull/1984
[#1983]: https://github.com/rectorphp/rector/pull/1983
[#1982]: https://github.com/rectorphp/rector/pull/1982
[#1980]: https://github.com/rectorphp/rector/pull/1980
[#1979]: https://github.com/rectorphp/rector/pull/1979
[#1978]: https://github.com/rectorphp/rector/pull/1978
[#1976]: https://github.com/rectorphp/rector/pull/1976
[#1972]: https://github.com/rectorphp/rector/pull/1972
[#1971]: https://github.com/rectorphp/rector/pull/1971
[#1969]: https://github.com/rectorphp/rector/pull/1969
[#1968]: https://github.com/rectorphp/rector/pull/1968
[#1967]: https://github.com/rectorphp/rector/pull/1967
[#1966]: https://github.com/rectorphp/rector/pull/1966
[#1962]: https://github.com/rectorphp/rector/pull/1962
[#1961]: https://github.com/rectorphp/rector/pull/1961
[#1958]: https://github.com/rectorphp/rector/pull/1958
[#1957]: https://github.com/rectorphp/rector/pull/1957
[#1955]: https://github.com/rectorphp/rector/pull/1955
[#1954]: https://github.com/rectorphp/rector/pull/1954
[#1953]: https://github.com/rectorphp/rector/pull/1953
[#1952]: https://github.com/rectorphp/rector/pull/1952
[#1951]: https://github.com/rectorphp/rector/pull/1951
[#1948]: https://github.com/rectorphp/rector/pull/1948
[#1947]: https://github.com/rectorphp/rector/pull/1947
[#1946]: https://github.com/rectorphp/rector/pull/1946
[#1943]: https://github.com/rectorphp/rector/pull/1943
[#1940]: https://github.com/rectorphp/rector/pull/1940
[#1939]: https://github.com/rectorphp/rector/pull/1939
[#1938]: https://github.com/rectorphp/rector/pull/1938
[#1937]: https://github.com/rectorphp/rector/pull/1937
[#1935]: https://github.com/rectorphp/rector/pull/1935
[#1934]: https://github.com/rectorphp/rector/pull/1934
[#1933]: https://github.com/rectorphp/rector/pull/1933
[#1932]: https://github.com/rectorphp/rector/pull/1932
[#1931]: https://github.com/rectorphp/rector/pull/1931
[#1928]: https://github.com/rectorphp/rector/pull/1928
[#1927]: https://github.com/rectorphp/rector/pull/1927
[#1926]: https://github.com/rectorphp/rector/pull/1926
[#1925]: https://github.com/rectorphp/rector/pull/1925
[#1924]: https://github.com/rectorphp/rector/pull/1924
[#1923]: https://github.com/rectorphp/rector/pull/1923
[#1922]: https://github.com/rectorphp/rector/pull/1922
[#1921]: https://github.com/rectorphp/rector/pull/1921
[#1917]: https://github.com/rectorphp/rector/pull/1917
[#1916]: https://github.com/rectorphp/rector/pull/1916
[#1915]: https://github.com/rectorphp/rector/pull/1915
[#1914]: https://github.com/rectorphp/rector/pull/1914
[#1912]: https://github.com/rectorphp/rector/pull/1912
[#1910]: https://github.com/rectorphp/rector/pull/1910
[#1908]: https://github.com/rectorphp/rector/pull/1908
[#1906]: https://github.com/rectorphp/rector/pull/1906
[#1903]: https://github.com/rectorphp/rector/pull/1903
[#1902]: https://github.com/rectorphp/rector/pull/1902
[#1901]: https://github.com/rectorphp/rector/pull/1901
[#1898]: https://github.com/rectorphp/rector/pull/1898
[#1897]: https://github.com/rectorphp/rector/pull/1897
[#1896]: https://github.com/rectorphp/rector/pull/1896
[#1869]: https://github.com/rectorphp/rector/pull/1869
[#1866]: https://github.com/rectorphp/rector/pull/1866
[#1438]: https://github.com/rectorphp/rector/pull/1438
[#3]: https://github.com/rectorphp/rector/pull/3
[v0.5.12]: https://github.com/rectorphp/rector/compare/v0.5.11...v0.5.12
[@see]: https://github.com/see
[@drbyte]: https://github.com/drbyte
[@atierant]: https://github.com/atierant
[@adrozdek]: https://github.com/adrozdek
[@HypeMC]: https://github.com/HypeMC
[@ADmad]: https://github.com/ADmad
[v0.5.11]: https://github.com/rectorphp/rector/compare/v0.5.10...v0.5.11
[#2125]: https://github.com/rectorphp/rector/pull/2125
[#2124]: https://github.com/rectorphp/rector/pull/2124
[#2123]: https://github.com/rectorphp/rector/pull/2123
[#2122]: https://github.com/rectorphp/rector/pull/2122
[#2121]: https://github.com/rectorphp/rector/pull/2121
[#2115]: https://github.com/rectorphp/rector/pull/2115
[#2112]: https://github.com/rectorphp/rector/pull/2112
[#2109]: https://github.com/rectorphp/rector/pull/2109
[#2107]: https://github.com/rectorphp/rector/pull/2107
[#2103]: https://github.com/rectorphp/rector/pull/2103
[#2102]: https://github.com/rectorphp/rector/pull/2102
[#2101]: https://github.com/rectorphp/rector/pull/2101
[#2100]: https://github.com/rectorphp/rector/pull/2100
[#2099]: https://github.com/rectorphp/rector/pull/2099
[#2097]: https://github.com/rectorphp/rector/pull/2097
[#2096]: https://github.com/rectorphp/rector/pull/2096
[#2095]: https://github.com/rectorphp/rector/pull/2095
[#2094]: https://github.com/rectorphp/rector/pull/2094
[#2093]: https://github.com/rectorphp/rector/pull/2093
[#2091]: https://github.com/rectorphp/rector/pull/2091
[#2088]: https://github.com/rectorphp/rector/pull/2088
[#2084]: https://github.com/rectorphp/rector/pull/2084
[#2083]: https://github.com/rectorphp/rector/pull/2083
[#2082]: https://github.com/rectorphp/rector/pull/2082
[#2081]: https://github.com/rectorphp/rector/pull/2081
[#2080]: https://github.com/rectorphp/rector/pull/2080
[#2079]: https://github.com/rectorphp/rector/pull/2079
[#2078]: https://github.com/rectorphp/rector/pull/2078
[#2077]: https://github.com/rectorphp/rector/pull/2077
[#2076]: https://github.com/rectorphp/rector/pull/2076
[#2075]: https://github.com/rectorphp/rector/pull/2075
[#2074]: https://github.com/rectorphp/rector/pull/2074
[#2073]: https://github.com/rectorphp/rector/pull/2073
[#2072]: https://github.com/rectorphp/rector/pull/2072
[#2070]: https://github.com/rectorphp/rector/pull/2070
[#2067]: https://github.com/rectorphp/rector/pull/2067
[#2065]: https://github.com/rectorphp/rector/pull/2065
[#2062]: https://github.com/rectorphp/rector/pull/2062
[#2060]: https://github.com/rectorphp/rector/pull/2060
[#2054]: https://github.com/rectorphp/rector/pull/2054
[#2052]: https://github.com/rectorphp/rector/pull/2052
[#2049]: https://github.com/rectorphp/rector/pull/2049
[#2047]: https://github.com/rectorphp/rector/pull/2047
[#2009]: https://github.com/rectorphp/rector/pull/2009
[@jeroenherczeg]: https://github.com/jeroenherczeg
[@TODO]: https://github.com/TODO
[v0.5.13]: https://github.com/rectorphp/rector/compare/v0.5.12...v0.5.13
[#2166]: https://github.com/rectorphp/rector/pull/2166
[#2165]: https://github.com/rectorphp/rector/pull/2165
[#2164]: https://github.com/rectorphp/rector/pull/2164
[#2162]: https://github.com/rectorphp/rector/pull/2162
[#2159]: https://github.com/rectorphp/rector/pull/2159
[#2156]: https://github.com/rectorphp/rector/pull/2156
[#2155]: https://github.com/rectorphp/rector/pull/2155
[#2152]: https://github.com/rectorphp/rector/pull/2152
[#2151]: https://github.com/rectorphp/rector/pull/2151
[#2150]: https://github.com/rectorphp/rector/pull/2150
[#2149]: https://github.com/rectorphp/rector/pull/2149
[#2148]: https://github.com/rectorphp/rector/pull/2148
[#2147]: https://github.com/rectorphp/rector/pull/2147
[#2146]: https://github.com/rectorphp/rector/pull/2146
[#2145]: https://github.com/rectorphp/rector/pull/2145
[#2144]: https://github.com/rectorphp/rector/pull/2144
[#2142]: https://github.com/rectorphp/rector/pull/2142
[#2141]: https://github.com/rectorphp/rector/pull/2141
[#2140]: https://github.com/rectorphp/rector/pull/2140
[#2139]: https://github.com/rectorphp/rector/pull/2139
[#2135]: https://github.com/rectorphp/rector/pull/2135
[#2132]: https://github.com/rectorphp/rector/pull/2132
[#2130]: https://github.com/rectorphp/rector/pull/2130
[#2128]: https://github.com/rectorphp/rector/pull/2128
[#2114]: https://github.com/rectorphp/rector/pull/2114
[#2090]: https://github.com/rectorphp/rector/pull/2090
[#2087]: https://github.com/rectorphp/rector/pull/2087
[v0.5.16]: https://github.com/rectorphp/rector/compare/v0.5.15...v0.5.16
[@lapetr]: https://github.com/lapetr
[v0.5.15]: https://github.com/rectorphp/rector/compare/v0.5.14...v0.5.15
[v0.5.14]: https://github.com/rectorphp/rector/compare/v0.5.13...v0.5.14
[#2195]: https://github.com/rectorphp/rector/pull/2195
[#2194]: https://github.com/rectorphp/rector/pull/2194
[#2193]: https://github.com/rectorphp/rector/pull/2193
[#2192]: https://github.com/rectorphp/rector/pull/2192
[#2191]: https://github.com/rectorphp/rector/pull/2191
[#2190]: https://github.com/rectorphp/rector/pull/2190
[#2188]: https://github.com/rectorphp/rector/pull/2188
[#2184]: https://github.com/rectorphp/rector/pull/2184
[#2183]: https://github.com/rectorphp/rector/pull/2183
[#2182]: https://github.com/rectorphp/rector/pull/2182
[#2181]: https://github.com/rectorphp/rector/pull/2181
[#2180]: https://github.com/rectorphp/rector/pull/2180
[#2177]: https://github.com/rectorphp/rector/pull/2177
[#2176]: https://github.com/rectorphp/rector/pull/2176
[#2175]: https://github.com/rectorphp/rector/pull/2175
[#2174]: https://github.com/rectorphp/rector/pull/2174
[#2172]: https://github.com/rectorphp/rector/pull/2172
[#2169]: https://github.com/rectorphp/rector/pull/2169
[#2168]: https://github.com/rectorphp/rector/pull/2168
[#2158]: https://github.com/rectorphp/rector/pull/2158
[#2157]: https://github.com/rectorphp/rector/pull/2157
[v0.5.19]: https://github.com/rectorphp/rector/compare/v0.5.18...v0.5.19
[v0.5.18]: https://github.com/rectorphp/rector/compare/v0.5.17...v0.5.18
[@stedekay]: https://github.com/stedekay
[@sabbelasichon]: https://github.com/sabbelasichon
[@dpesch]: https://github.com/dpesch
[@SilverFire]: https://github.com/SilverFire
[v0.5.17]: https://github.com/rectorphp/rector/compare/v0.5.16...v0.5.17
[#2328]: https://github.com/rectorphp/rector/pull/2328
[#2327]: https://github.com/rectorphp/rector/pull/2327
[#2326]: https://github.com/rectorphp/rector/pull/2326
[#2325]: https://github.com/rectorphp/rector/pull/2325
[#2324]: https://github.com/rectorphp/rector/pull/2324
[#2323]: https://github.com/rectorphp/rector/pull/2323
[#2321]: https://github.com/rectorphp/rector/pull/2321
[#2318]: https://github.com/rectorphp/rector/pull/2318
[#2317]: https://github.com/rectorphp/rector/pull/2317
[#2315]: https://github.com/rectorphp/rector/pull/2315
[#2311]: https://github.com/rectorphp/rector/pull/2311
[#2310]: https://github.com/rectorphp/rector/pull/2310
[#2309]: https://github.com/rectorphp/rector/pull/2309
[#2308]: https://github.com/rectorphp/rector/pull/2308
[#2306]: https://github.com/rectorphp/rector/pull/2306
[#2302]: https://github.com/rectorphp/rector/pull/2302
[#2300]: https://github.com/rectorphp/rector/pull/2300
[#2299]: https://github.com/rectorphp/rector/pull/2299
[#2297]: https://github.com/rectorphp/rector/pull/2297
[#2294]: https://github.com/rectorphp/rector/pull/2294
[#2293]: https://github.com/rectorphp/rector/pull/2293
[#2292]: https://github.com/rectorphp/rector/pull/2292
[#2291]: https://github.com/rectorphp/rector/pull/2291
[#2289]: https://github.com/rectorphp/rector/pull/2289
[#2288]: https://github.com/rectorphp/rector/pull/2288
[#2284]: https://github.com/rectorphp/rector/pull/2284
[#2282]: https://github.com/rectorphp/rector/pull/2282
[#2281]: https://github.com/rectorphp/rector/pull/2281
[#2278]: https://github.com/rectorphp/rector/pull/2278
[#2277]: https://github.com/rectorphp/rector/pull/2277
[#2275]: https://github.com/rectorphp/rector/pull/2275
[#2273]: https://github.com/rectorphp/rector/pull/2273
[#2269]: https://github.com/rectorphp/rector/pull/2269
[#2264]: https://github.com/rectorphp/rector/pull/2264
[#2263]: https://github.com/rectorphp/rector/pull/2263
[#2262]: https://github.com/rectorphp/rector/pull/2262
[#2261]: https://github.com/rectorphp/rector/pull/2261
[#2259]: https://github.com/rectorphp/rector/pull/2259
[#2258]: https://github.com/rectorphp/rector/pull/2258
[#2257]: https://github.com/rectorphp/rector/pull/2257
[#2255]: https://github.com/rectorphp/rector/pull/2255
[#2254]: https://github.com/rectorphp/rector/pull/2254
[#2253]: https://github.com/rectorphp/rector/pull/2253
[#2252]: https://github.com/rectorphp/rector/pull/2252
[#2251]: https://github.com/rectorphp/rector/pull/2251
[#2250]: https://github.com/rectorphp/rector/pull/2250
[#2249]: https://github.com/rectorphp/rector/pull/2249
[#2248]: https://github.com/rectorphp/rector/pull/2248
[#2247]: https://github.com/rectorphp/rector/pull/2247
[#2246]: https://github.com/rectorphp/rector/pull/2246
[#2240]: https://github.com/rectorphp/rector/pull/2240
[#2239]: https://github.com/rectorphp/rector/pull/2239
[#2238]: https://github.com/rectorphp/rector/pull/2238
[#2237]: https://github.com/rectorphp/rector/pull/2237
[#2236]: https://github.com/rectorphp/rector/pull/2236
[#2235]: https://github.com/rectorphp/rector/pull/2235
[#2234]: https://github.com/rectorphp/rector/pull/2234
[#2233]: https://github.com/rectorphp/rector/pull/2233
[#2231]: https://github.com/rectorphp/rector/pull/2231
[#2224]: https://github.com/rectorphp/rector/pull/2224
[#2223]: https://github.com/rectorphp/rector/pull/2223
[#2222]: https://github.com/rectorphp/rector/pull/2222
[#2221]: https://github.com/rectorphp/rector/pull/2221
[#2220]: https://github.com/rectorphp/rector/pull/2220
[#2218]: https://github.com/rectorphp/rector/pull/2218
[#2217]: https://github.com/rectorphp/rector/pull/2217
[#2214]: https://github.com/rectorphp/rector/pull/2214
[#2211]: https://github.com/rectorphp/rector/pull/2211
[#2207]: https://github.com/rectorphp/rector/pull/2207
[#2206]: https://github.com/rectorphp/rector/pull/2206
[#2203]: https://github.com/rectorphp/rector/pull/2203
[#2202]: https://github.com/rectorphp/rector/pull/2202
[#2200]: https://github.com/rectorphp/rector/pull/2200
[#2198]: https://github.com/rectorphp/rector/pull/2198
[#2197]: https://github.com/rectorphp/rector/pull/2197
[#2196]: https://github.com/rectorphp/rector/pull/2196
[#2187]: https://github.com/rectorphp/rector/pull/2187
[v0.5.22]: https://github.com/rectorphp/rector/compare/v0.5.21...v0.5.22
[v0.5.21]: https://github.com/rectorphp/rector/compare/v0.5.20...v0.5.21
[@sbine]: https://github.com/sbine
[@orklah]: https://github.com/orklah
[@nissim94]: https://github.com/nissim94
[@franmomu]: https://github.com/franmomu
[v0.5.20]: https://github.com/rectorphp/rector/compare/v0.5.19...v0.5.20
[#2349]: https://github.com/rectorphp/rector/pull/2349
[#2346]: https://github.com/rectorphp/rector/pull/2346
[#2344]: https://github.com/rectorphp/rector/pull/2344
[#2343]: https://github.com/rectorphp/rector/pull/2343
[#2341]: https://github.com/rectorphp/rector/pull/2341
[#2338]: https://github.com/rectorphp/rector/pull/2338
[#2337]: https://github.com/rectorphp/rector/pull/2337
[#2332]: https://github.com/rectorphp/rector/pull/2332
[#2331]: https://github.com/rectorphp/rector/pull/2331
[#2329]: https://github.com/rectorphp/rector/pull/2329
[@fsok]: https://github.com/fsok
[v0.5.23]: https://github.com/rectorphp/rector/compare/v0.5.22...v0.5.23
[#2414]: https://github.com/rectorphp/rector/pull/2414
[#2411]: https://github.com/rectorphp/rector/pull/2411
[#2410]: https://github.com/rectorphp/rector/pull/2410
[#2409]: https://github.com/rectorphp/rector/pull/2409
[#2407]: https://github.com/rectorphp/rector/pull/2407
[#2406]: https://github.com/rectorphp/rector/pull/2406
[#2404]: https://github.com/rectorphp/rector/pull/2404
[#2400]: https://github.com/rectorphp/rector/pull/2400
[#2397]: https://github.com/rectorphp/rector/pull/2397
[#2396]: https://github.com/rectorphp/rector/pull/2396
[#2395]: https://github.com/rectorphp/rector/pull/2395
[#2394]: https://github.com/rectorphp/rector/pull/2394
[#2393]: https://github.com/rectorphp/rector/pull/2393
[#2392]: https://github.com/rectorphp/rector/pull/2392
[#2391]: https://github.com/rectorphp/rector/pull/2391
[#2390]: https://github.com/rectorphp/rector/pull/2390
[#2389]: https://github.com/rectorphp/rector/pull/2389
[#2386]: https://github.com/rectorphp/rector/pull/2386
[#2385]: https://github.com/rectorphp/rector/pull/2385
[#2381]: https://github.com/rectorphp/rector/pull/2381
[#2378]: https://github.com/rectorphp/rector/pull/2378
[#2374]: https://github.com/rectorphp/rector/pull/2374
[#2373]: https://github.com/rectorphp/rector/pull/2373
[#2372]: https://github.com/rectorphp/rector/pull/2372
[#2371]: https://github.com/rectorphp/rector/pull/2371
[#2369]: https://github.com/rectorphp/rector/pull/2369
[#2368]: https://github.com/rectorphp/rector/pull/2368
[#2359]: https://github.com/rectorphp/rector/pull/2359
[#2358]: https://github.com/rectorphp/rector/pull/2358
[#2353]: https://github.com/rectorphp/rector/pull/2353
[#2352]: https://github.com/rectorphp/rector/pull/2352
[#2351]: https://github.com/rectorphp/rector/pull/2351
[#2350]: https://github.com/rectorphp/rector/pull/2350
[#2347]: https://github.com/rectorphp/rector/pull/2347
[@staabm]: https://github.com/staabm
[@sojki]: https://github.com/sojki
[@ruudboon]: https://github.com/ruudboon
[@radimvaculik]: https://github.com/radimvaculik
[@mallardduck]: https://github.com/mallardduck
[@danielroe]: https://github.com/danielroe
[@EmanueleMinotto]: https://github.com/EmanueleMinotto
[v0.6.0]: https://github.com/rectorphp/rector/compare/v0.5.23...v0.6.0
[#2450]: https://github.com/rectorphp/rector/pull/2450
[#2448]: https://github.com/rectorphp/rector/pull/2448
[#2447]: https://github.com/rectorphp/rector/pull/2447
[#2442]: https://github.com/rectorphp/rector/pull/2442
[#2439]: https://github.com/rectorphp/rector/pull/2439
[#2438]: https://github.com/rectorphp/rector/pull/2438
[#2437]: https://github.com/rectorphp/rector/pull/2437
[#2436]: https://github.com/rectorphp/rector/pull/2436
[#2435]: https://github.com/rectorphp/rector/pull/2435
[#2428]: https://github.com/rectorphp/rector/pull/2428
[#2427]: https://github.com/rectorphp/rector/pull/2427
[#2420]: https://github.com/rectorphp/rector/pull/2420
[@andreybolonin]: https://github.com/andreybolonin
[@RusiPapazov]: https://github.com/RusiPapazov
[v0.6.1]: https://github.com/rectorphp/rector/compare/v0.6.0...v0.6.1
[#2502]: https://github.com/rectorphp/rector/pull/2502
[#2501]: https://github.com/rectorphp/rector/pull/2501
[#2500]: https://github.com/rectorphp/rector/pull/2500
[#2499]: https://github.com/rectorphp/rector/pull/2499
[#2497]: https://github.com/rectorphp/rector/pull/2497
[#2496]: https://github.com/rectorphp/rector/pull/2496
[#2493]: https://github.com/rectorphp/rector/pull/2493
[#2492]: https://github.com/rectorphp/rector/pull/2492
[#2491]: https://github.com/rectorphp/rector/pull/2491
[#2489]: https://github.com/rectorphp/rector/pull/2489
[#2487]: https://github.com/rectorphp/rector/pull/2487
[#2486]: https://github.com/rectorphp/rector/pull/2486
[#2485]: https://github.com/rectorphp/rector/pull/2485
[#2484]: https://github.com/rectorphp/rector/pull/2484
[#2483]: https://github.com/rectorphp/rector/pull/2483
[#2482]: https://github.com/rectorphp/rector/pull/2482
[#2481]: https://github.com/rectorphp/rector/pull/2481
[#2480]: https://github.com/rectorphp/rector/pull/2480
[#2479]: https://github.com/rectorphp/rector/pull/2479
[#2478]: https://github.com/rectorphp/rector/pull/2478
[#2476]: https://github.com/rectorphp/rector/pull/2476
[#2475]: https://github.com/rectorphp/rector/pull/2475
[#2472]: https://github.com/rectorphp/rector/pull/2472
[#2470]: https://github.com/rectorphp/rector/pull/2470
[#2467]: https://github.com/rectorphp/rector/pull/2467
[#2466]: https://github.com/rectorphp/rector/pull/2466
[#2465]: https://github.com/rectorphp/rector/pull/2465
[#2464]: https://github.com/rectorphp/rector/pull/2464
[#2463]: https://github.com/rectorphp/rector/pull/2463
[#2461]: https://github.com/rectorphp/rector/pull/2461
[#2459]: https://github.com/rectorphp/rector/pull/2459
[#2458]: https://github.com/rectorphp/rector/pull/2458
[#2457]: https://github.com/rectorphp/rector/pull/2457
[v0.6.4]: https://github.com/rectorphp/rector/compare/v0.6.3...v0.6.4
[v0.6.3]: https://github.com/rectorphp/rector/compare/v0.6.2...v0.6.3
[v0.6.2]: https://github.com/rectorphp/rector/compare/v0.6.1...v0.6.2
[@ruudk]: https://github.com/ruudk
[@lulco]: https://github.com/lulco
[#2536]: https://github.com/rectorphp/rector/pull/2536
[#2534]: https://github.com/rectorphp/rector/pull/2534
[#2533]: https://github.com/rectorphp/rector/pull/2533
[#2532]: https://github.com/rectorphp/rector/pull/2532
[#2531]: https://github.com/rectorphp/rector/pull/2531
[#2530]: https://github.com/rectorphp/rector/pull/2530
[#2529]: https://github.com/rectorphp/rector/pull/2529
[#2528]: https://github.com/rectorphp/rector/pull/2528
[#2527]: https://github.com/rectorphp/rector/pull/2527
[#2526]: https://github.com/rectorphp/rector/pull/2526
[#2524]: https://github.com/rectorphp/rector/pull/2524
[#2523]: https://github.com/rectorphp/rector/pull/2523
[#2520]: https://github.com/rectorphp/rector/pull/2520
[#2519]: https://github.com/rectorphp/rector/pull/2519
[#2518]: https://github.com/rectorphp/rector/pull/2518
[#2517]: https://github.com/rectorphp/rector/pull/2517
[#2514]: https://github.com/rectorphp/rector/pull/2514
[#2512]: https://github.com/rectorphp/rector/pull/2512
[#2511]: https://github.com/rectorphp/rector/pull/2511
[#2510]: https://github.com/rectorphp/rector/pull/2510
[#2509]: https://github.com/rectorphp/rector/pull/2509
[#2508]: https://github.com/rectorphp/rector/pull/2508
[#2507]: https://github.com/rectorphp/rector/pull/2507
[#2505]: https://github.com/rectorphp/rector/pull/2505
[#2503]: https://github.com/rectorphp/rector/pull/2503
[#2598]: https://github.com/rectorphp/rector/pull/2598
[#2595]: https://github.com/rectorphp/rector/pull/2595
[#2593]: https://github.com/rectorphp/rector/pull/2593
[#2592]: https://github.com/rectorphp/rector/pull/2592
[#2591]: https://github.com/rectorphp/rector/pull/2591
[#2589]: https://github.com/rectorphp/rector/pull/2589
[#2588]: https://github.com/rectorphp/rector/pull/2588
[#2586]: https://github.com/rectorphp/rector/pull/2586
[#2584]: https://github.com/rectorphp/rector/pull/2584
[#2583]: https://github.com/rectorphp/rector/pull/2583
[#2582]: https://github.com/rectorphp/rector/pull/2582
[#2581]: https://github.com/rectorphp/rector/pull/2581
[#2576]: https://github.com/rectorphp/rector/pull/2576
[#2575]: https://github.com/rectorphp/rector/pull/2575
[#2572]: https://github.com/rectorphp/rector/pull/2572
[#2570]: https://github.com/rectorphp/rector/pull/2570
[#2569]: https://github.com/rectorphp/rector/pull/2569
[#2568]: https://github.com/rectorphp/rector/pull/2568
[#2567]: https://github.com/rectorphp/rector/pull/2567
[#2566]: https://github.com/rectorphp/rector/pull/2566
[#2565]: https://github.com/rectorphp/rector/pull/2565
[#2563]: https://github.com/rectorphp/rector/pull/2563
[#2562]: https://github.com/rectorphp/rector/pull/2562
[#2561]: https://github.com/rectorphp/rector/pull/2561
[#2559]: https://github.com/rectorphp/rector/pull/2559
[#2558]: https://github.com/rectorphp/rector/pull/2558
[#2557]: https://github.com/rectorphp/rector/pull/2557
[#2553]: https://github.com/rectorphp/rector/pull/2553
[#2550]: https://github.com/rectorphp/rector/pull/2550
[#2548]: https://github.com/rectorphp/rector/pull/2548
[#2547]: https://github.com/rectorphp/rector/pull/2547
[#2541]: https://github.com/rectorphp/rector/pull/2541
[#2538]: https://github.com/rectorphp/rector/pull/2538
[v0.6.6]: https://github.com/rectorphp/rector/compare/v0.6.5...v0.6.6
[v0.6.5]: https://github.com/rectorphp/rector/compare/v0.6.4...v0.6.5
[@ondrejmirtes]: https://github.com/ondrejmirtes
[@implements]: https://github.com/implements
[@extends]: https://github.com/extends
[#2607]: https://github.com/rectorphp/rector/pull/2607
[#2605]: https://github.com/rectorphp/rector/pull/2605
[#2603]: https://github.com/rectorphp/rector/pull/2603
[#2601]: https://github.com/rectorphp/rector/pull/2601
[#2600]: https://github.com/rectorphp/rector/pull/2600
[#2599]: https://github.com/rectorphp/rector/pull/2599
[v0.6.8]: https://github.com/rectorphp/rector/compare/v0.6.7...v0.6.8
[v0.6.7]: https://github.com/rectorphp/rector/compare/v0.6.6...v0.6.7
[@ghostika]: https://github.com/ghostika
[@Jaapze]: https://github.com/Jaapze
[#2692]: https://github.com/rectorphp/rector/pull/2692
[#2690]: https://github.com/rectorphp/rector/pull/2690
[#2687]: https://github.com/rectorphp/rector/pull/2687
[#2686]: https://github.com/rectorphp/rector/pull/2686
[#2685]: https://github.com/rectorphp/rector/pull/2685
[#2683]: https://github.com/rectorphp/rector/pull/2683
[#2682]: https://github.com/rectorphp/rector/pull/2682
[#2681]: https://github.com/rectorphp/rector/pull/2681
[#2676]: https://github.com/rectorphp/rector/pull/2676
[#2674]: https://github.com/rectorphp/rector/pull/2674
[#2673]: https://github.com/rectorphp/rector/pull/2673
[#2672]: https://github.com/rectorphp/rector/pull/2672
[#2671]: https://github.com/rectorphp/rector/pull/2671
[#2670]: https://github.com/rectorphp/rector/pull/2670
[#2668]: https://github.com/rectorphp/rector/pull/2668
[#2666]: https://github.com/rectorphp/rector/pull/2666
[#2664]: https://github.com/rectorphp/rector/pull/2664
[#2663]: https://github.com/rectorphp/rector/pull/2663
[#2662]: https://github.com/rectorphp/rector/pull/2662
[#2660]: https://github.com/rectorphp/rector/pull/2660
[#2659]: https://github.com/rectorphp/rector/pull/2659
[#2658]: https://github.com/rectorphp/rector/pull/2658
[#2657]: https://github.com/rectorphp/rector/pull/2657
[#2655]: https://github.com/rectorphp/rector/pull/2655
[#2654]: https://github.com/rectorphp/rector/pull/2654
[#2649]: https://github.com/rectorphp/rector/pull/2649
[#2648]: https://github.com/rectorphp/rector/pull/2648
[#2646]: https://github.com/rectorphp/rector/pull/2646
[#2645]: https://github.com/rectorphp/rector/pull/2645
[#2644]: https://github.com/rectorphp/rector/pull/2644
[#2643]: https://github.com/rectorphp/rector/pull/2643
[#2641]: https://github.com/rectorphp/rector/pull/2641
[#2640]: https://github.com/rectorphp/rector/pull/2640
[#2639]: https://github.com/rectorphp/rector/pull/2639
[#2638]: https://github.com/rectorphp/rector/pull/2638
[#2637]: https://github.com/rectorphp/rector/pull/2637
[#2636]: https://github.com/rectorphp/rector/pull/2636
[#2635]: https://github.com/rectorphp/rector/pull/2635
[#2633]: https://github.com/rectorphp/rector/pull/2633
[#2629]: https://github.com/rectorphp/rector/pull/2629
[#2625]: https://github.com/rectorphp/rector/pull/2625
[#2624]: https://github.com/rectorphp/rector/pull/2624
[#2623]: https://github.com/rectorphp/rector/pull/2623
[#2622]: https://github.com/rectorphp/rector/pull/2622
[#2619]: https://github.com/rectorphp/rector/pull/2619
[#2618]: https://github.com/rectorphp/rector/pull/2618
[#2617]: https://github.com/rectorphp/rector/pull/2617
[#2613]: https://github.com/rectorphp/rector/pull/2613
[#2610]: https://github.com/rectorphp/rector/pull/2610
[#2609]: https://github.com/rectorphp/rector/pull/2609
[#2608]: https://github.com/rectorphp/rector/pull/2608
[#2587]: https://github.com/rectorphp/rector/pull/2587
[#2546]: https://github.com/rectorphp/rector/pull/2546
[v0.6.9]: https://github.com/rectorphp/rector/compare/v0.6.8...v0.6.9
[v0.6.11]: https://github.com/rectorphp/rector/compare/v0.6.10...v0.6.11
[v0.6.10]: https://github.com/rectorphp/rector/compare/v0.6.9...v0.6.10
[@template]: https://github.com/template
[@palpalani]: https://github.com/palpalani
[@Lctrs]: https://github.com/Lctrs
[@C0pyR1ght]: https://github.com/C0pyR1ght
[#2866]: https://github.com/rectorphp/rector/pull/2866
[#2865]: https://github.com/rectorphp/rector/pull/2865
[#2860]: https://github.com/rectorphp/rector/pull/2860
[#2859]: https://github.com/rectorphp/rector/pull/2859
[#2858]: https://github.com/rectorphp/rector/pull/2858
[#2857]: https://github.com/rectorphp/rector/pull/2857
[#2856]: https://github.com/rectorphp/rector/pull/2856
[#2853]: https://github.com/rectorphp/rector/pull/2853
[#2851]: https://github.com/rectorphp/rector/pull/2851
[#2850]: https://github.com/rectorphp/rector/pull/2850
[#2849]: https://github.com/rectorphp/rector/pull/2849
[#2848]: https://github.com/rectorphp/rector/pull/2848
[#2847]: https://github.com/rectorphp/rector/pull/2847
[#2846]: https://github.com/rectorphp/rector/pull/2846
[#2845]: https://github.com/rectorphp/rector/pull/2845
[#2844]: https://github.com/rectorphp/rector/pull/2844
[#2843]: https://github.com/rectorphp/rector/pull/2843
[#2841]: https://github.com/rectorphp/rector/pull/2841
[#2840]: https://github.com/rectorphp/rector/pull/2840
[#2838]: https://github.com/rectorphp/rector/pull/2838
[#2831]: https://github.com/rectorphp/rector/pull/2831
[#2830]: https://github.com/rectorphp/rector/pull/2830
[#2829]: https://github.com/rectorphp/rector/pull/2829
[#2827]: https://github.com/rectorphp/rector/pull/2827
[#2826]: https://github.com/rectorphp/rector/pull/2826
[#2825]: https://github.com/rectorphp/rector/pull/2825
[#2823]: https://github.com/rectorphp/rector/pull/2823
[#2821]: https://github.com/rectorphp/rector/pull/2821
[#2819]: https://github.com/rectorphp/rector/pull/2819
[#2818]: https://github.com/rectorphp/rector/pull/2818
[#2817]: https://github.com/rectorphp/rector/pull/2817
[#2816]: https://github.com/rectorphp/rector/pull/2816
[#2814]: https://github.com/rectorphp/rector/pull/2814
[#2813]: https://github.com/rectorphp/rector/pull/2813
[#2812]: https://github.com/rectorphp/rector/pull/2812
[#2811]: https://github.com/rectorphp/rector/pull/2811
[#2810]: https://github.com/rectorphp/rector/pull/2810
[#2808]: https://github.com/rectorphp/rector/pull/2808
[#2807]: https://github.com/rectorphp/rector/pull/2807
[#2802]: https://github.com/rectorphp/rector/pull/2802
[#2801]: https://github.com/rectorphp/rector/pull/2801
[#2800]: https://github.com/rectorphp/rector/pull/2800
[#2799]: https://github.com/rectorphp/rector/pull/2799
[#2798]: https://github.com/rectorphp/rector/pull/2798
[#2797]: https://github.com/rectorphp/rector/pull/2797
[#2795]: https://github.com/rectorphp/rector/pull/2795
[#2794]: https://github.com/rectorphp/rector/pull/2794
[#2792]: https://github.com/rectorphp/rector/pull/2792
[#2791]: https://github.com/rectorphp/rector/pull/2791
[#2790]: https://github.com/rectorphp/rector/pull/2790
[#2789]: https://github.com/rectorphp/rector/pull/2789
[#2787]: https://github.com/rectorphp/rector/pull/2787
[#2786]: https://github.com/rectorphp/rector/pull/2786
[#2784]: https://github.com/rectorphp/rector/pull/2784
[#2783]: https://github.com/rectorphp/rector/pull/2783
[#2781]: https://github.com/rectorphp/rector/pull/2781
[#2780]: https://github.com/rectorphp/rector/pull/2780
[#2775]: https://github.com/rectorphp/rector/pull/2775
[#2773]: https://github.com/rectorphp/rector/pull/2773
[#2772]: https://github.com/rectorphp/rector/pull/2772
[#2771]: https://github.com/rectorphp/rector/pull/2771
[#2770]: https://github.com/rectorphp/rector/pull/2770
[#2769]: https://github.com/rectorphp/rector/pull/2769
[#2768]: https://github.com/rectorphp/rector/pull/2768
[#2767]: https://github.com/rectorphp/rector/pull/2767
[#2762]: https://github.com/rectorphp/rector/pull/2762
[#2761]: https://github.com/rectorphp/rector/pull/2761
[#2759]: https://github.com/rectorphp/rector/pull/2759
[#2758]: https://github.com/rectorphp/rector/pull/2758
[#2757]: https://github.com/rectorphp/rector/pull/2757
[#2756]: https://github.com/rectorphp/rector/pull/2756
[#2752]: https://github.com/rectorphp/rector/pull/2752
[#2750]: https://github.com/rectorphp/rector/pull/2750
[#2747]: https://github.com/rectorphp/rector/pull/2747
[#2745]: https://github.com/rectorphp/rector/pull/2745
[#2744]: https://github.com/rectorphp/rector/pull/2744
[#2742]: https://github.com/rectorphp/rector/pull/2742
[#2741]: https://github.com/rectorphp/rector/pull/2741
[#2739]: https://github.com/rectorphp/rector/pull/2739
[#2737]: https://github.com/rectorphp/rector/pull/2737
[#2736]: https://github.com/rectorphp/rector/pull/2736
[#2735]: https://github.com/rectorphp/rector/pull/2735
[#2731]: https://github.com/rectorphp/rector/pull/2731
[#2728]: https://github.com/rectorphp/rector/pull/2728
[#2726]: https://github.com/rectorphp/rector/pull/2726
[#2723]: https://github.com/rectorphp/rector/pull/2723
[#2722]: https://github.com/rectorphp/rector/pull/2722
[#2720]: https://github.com/rectorphp/rector/pull/2720
[#2719]: https://github.com/rectorphp/rector/pull/2719
[#2718]: https://github.com/rectorphp/rector/pull/2718
[#2715]: https://github.com/rectorphp/rector/pull/2715
[#2714]: https://github.com/rectorphp/rector/pull/2714
[#2713]: https://github.com/rectorphp/rector/pull/2713
[#2712]: https://github.com/rectorphp/rector/pull/2712
[#2711]: https://github.com/rectorphp/rector/pull/2711
[#2710]: https://github.com/rectorphp/rector/pull/2710
[#2709]: https://github.com/rectorphp/rector/pull/2709
[#2708]: https://github.com/rectorphp/rector/pull/2708
[#2707]: https://github.com/rectorphp/rector/pull/2707
[#2706]: https://github.com/rectorphp/rector/pull/2706
[#2704]: https://github.com/rectorphp/rector/pull/2704
[#2703]: https://github.com/rectorphp/rector/pull/2703
[#2702]: https://github.com/rectorphp/rector/pull/2702
[#2700]: https://github.com/rectorphp/rector/pull/2700
[#2698]: https://github.com/rectorphp/rector/pull/2698
[#2694]: https://github.com/rectorphp/rector/pull/2694
[#2691]: https://github.com/rectorphp/rector/pull/2691
[#2650]: https://github.com/rectorphp/rector/pull/2650
[#2631]: https://github.com/rectorphp/rector/pull/2631
[#2630]: https://github.com/rectorphp/rector/pull/2630
[v0.7.0]: https://github.com/rectorphp/rector/compare/v0.6.14...v0.7.0
[v0.6.14]: https://github.com/rectorphp/rector/compare/v0.6.13...v0.6.14
[v0.6.13]: https://github.com/rectorphp/rector/compare/v0.6.12...v0.6.13
[v0.6.12]: https://github.com/rectorphp/rector/compare/v0.6.11...v0.6.12
[@zonuexe]: https://github.com/zonuexe
[@vladyslavstartsev]: https://github.com/vladyslavstartsev
[@ikvasnica]: https://github.com/ikvasnica
[@alfredbez]: https://github.com/alfredbez
[#2922]: https://github.com/rectorphp/rector/pull/2922
[#2921]: https://github.com/rectorphp/rector/pull/2921
[#2920]: https://github.com/rectorphp/rector/pull/2920
[#2919]: https://github.com/rectorphp/rector/pull/2919
[#2918]: https://github.com/rectorphp/rector/pull/2918
[#2917]: https://github.com/rectorphp/rector/pull/2917
[#2916]: https://github.com/rectorphp/rector/pull/2916
[#2915]: https://github.com/rectorphp/rector/pull/2915
[#2914]: https://github.com/rectorphp/rector/pull/2914
[#2913]: https://github.com/rectorphp/rector/pull/2913
[#2909]: https://github.com/rectorphp/rector/pull/2909
[#2907]: https://github.com/rectorphp/rector/pull/2907
[#2906]: https://github.com/rectorphp/rector/pull/2906
[#2905]: https://github.com/rectorphp/rector/pull/2905
[#2904]: https://github.com/rectorphp/rector/pull/2904
[#2903]: https://github.com/rectorphp/rector/pull/2903
[#2902]: https://github.com/rectorphp/rector/pull/2902
[#2901]: https://github.com/rectorphp/rector/pull/2901
[#2900]: https://github.com/rectorphp/rector/pull/2900
[#2899]: https://github.com/rectorphp/rector/pull/2899
[#2898]: https://github.com/rectorphp/rector/pull/2898
[#2897]: https://github.com/rectorphp/rector/pull/2897
[#2896]: https://github.com/rectorphp/rector/pull/2896
[#2893]: https://github.com/rectorphp/rector/pull/2893
[#2890]: https://github.com/rectorphp/rector/pull/2890
[#2886]: https://github.com/rectorphp/rector/pull/2886
[#2885]: https://github.com/rectorphp/rector/pull/2885
[#2884]: https://github.com/rectorphp/rector/pull/2884
[#2883]: https://github.com/rectorphp/rector/pull/2883
[#2881]: https://github.com/rectorphp/rector/pull/2881
[#2880]: https://github.com/rectorphp/rector/pull/2880
[#2876]: https://github.com/rectorphp/rector/pull/2876
[#2875]: https://github.com/rectorphp/rector/pull/2875
[#2874]: https://github.com/rectorphp/rector/pull/2874
[#2873]: https://github.com/rectorphp/rector/pull/2873
[#2872]: https://github.com/rectorphp/rector/pull/2872
[#2871]: https://github.com/rectorphp/rector/pull/2871
[#2870]: https://github.com/rectorphp/rector/pull/2870
[#2869]: https://github.com/rectorphp/rector/pull/2869
[#2868]: https://github.com/rectorphp/rector/pull/2868
[#2867]: https://github.com/rectorphp/rector/pull/2867
[#2863]: https://github.com/rectorphp/rector/pull/2863
[#2862]: https://github.com/rectorphp/rector/pull/2862
[@escopecz]: https://github.com/escopecz
[@Ivorius]: https://github.com/Ivorius
[#2941]: https://github.com/rectorphp/rector/pull/2941
[#2940]: https://github.com/rectorphp/rector/pull/2940
[#2937]: https://github.com/rectorphp/rector/pull/2937
[#2935]: https://github.com/rectorphp/rector/pull/2935
[#2934]: https://github.com/rectorphp/rector/pull/2934
[#2933]: https://github.com/rectorphp/rector/pull/2933
[#2932]: https://github.com/rectorphp/rector/pull/2932
[#2931]: https://github.com/rectorphp/rector/pull/2931
[#2926]: https://github.com/rectorphp/rector/pull/2926
[#2925]: https://github.com/rectorphp/rector/pull/2925
[#2924]: https://github.com/rectorphp/rector/pull/2924
[v0.7.1]: https://github.com/rectorphp/rector/compare/v0.7.0...v0.7.1
[@method]: https://github.com/method
[#2979]: https://github.com/rectorphp/rector/pull/2979
[#2978]: https://github.com/rectorphp/rector/pull/2978
[#2977]: https://github.com/rectorphp/rector/pull/2977
[#2975]: https://github.com/rectorphp/rector/pull/2975
[#2974]: https://github.com/rectorphp/rector/pull/2974
[#2972]: https://github.com/rectorphp/rector/pull/2972
[#2969]: https://github.com/rectorphp/rector/pull/2969
[#2968]: https://github.com/rectorphp/rector/pull/2968
[#2966]: https://github.com/rectorphp/rector/pull/2966
[#2965]: https://github.com/rectorphp/rector/pull/2965
[#2964]: https://github.com/rectorphp/rector/pull/2964
[#2963]: https://github.com/rectorphp/rector/pull/2963
[#2962]: https://github.com/rectorphp/rector/pull/2962
[#2961]: https://github.com/rectorphp/rector/pull/2961
[#2960]: https://github.com/rectorphp/rector/pull/2960
[#2959]: https://github.com/rectorphp/rector/pull/2959
[#2956]: https://github.com/rectorphp/rector/pull/2956
[#2954]: https://github.com/rectorphp/rector/pull/2954
[#2953]: https://github.com/rectorphp/rector/pull/2953
[#2952]: https://github.com/rectorphp/rector/pull/2952
[#2951]: https://github.com/rectorphp/rector/pull/2951
[#2950]: https://github.com/rectorphp/rector/pull/2950
[#2949]: https://github.com/rectorphp/rector/pull/2949
[#2948]: https://github.com/rectorphp/rector/pull/2948
[#2947]: https://github.com/rectorphp/rector/pull/2947
[#2946]: https://github.com/rectorphp/rector/pull/2946
[#2943]: https://github.com/rectorphp/rector/pull/2943
[#2939]: https://github.com/rectorphp/rector/pull/2939
[v0.7.3]: https://github.com/rectorphp/rector/compare/v0.7.2...v0.7.3
[v0.7.2]: https://github.com/rectorphp/rector/compare/v0.7.1...v0.7.2
[@alexanderschnitzler]: https://github.com/alexanderschnitzler
[#3012]: https://github.com/rectorphp/rector/pull/3012
[#3010]: https://github.com/rectorphp/rector/pull/3010
[#3009]: https://github.com/rectorphp/rector/pull/3009
[#3008]: https://github.com/rectorphp/rector/pull/3008
[#3005]: https://github.com/rectorphp/rector/pull/3005
[#3004]: https://github.com/rectorphp/rector/pull/3004
[#3003]: https://github.com/rectorphp/rector/pull/3003
[#2998]: https://github.com/rectorphp/rector/pull/2998
[#2997]: https://github.com/rectorphp/rector/pull/2997
[#2996]: https://github.com/rectorphp/rector/pull/2996
[#2992]: https://github.com/rectorphp/rector/pull/2992
[#2990]: https://github.com/rectorphp/rector/pull/2990
[#2988]: https://github.com/rectorphp/rector/pull/2988
[#2987]: https://github.com/rectorphp/rector/pull/2987
[#2985]: https://github.com/rectorphp/rector/pull/2985
[#2984]: https://github.com/rectorphp/rector/pull/2984
[#2982]: https://github.com/rectorphp/rector/pull/2982
[#2981]: https://github.com/rectorphp/rector/pull/2981
[#2980]: https://github.com/rectorphp/rector/pull/2980
[#3083]: https://github.com/rectorphp/rector/pull/3083
[#3082]: https://github.com/rectorphp/rector/pull/3082
[#3081]: https://github.com/rectorphp/rector/pull/3081
[#3080]: https://github.com/rectorphp/rector/pull/3080
[#3079]: https://github.com/rectorphp/rector/pull/3079
[#3078]: https://github.com/rectorphp/rector/pull/3078
[#3077]: https://github.com/rectorphp/rector/pull/3077
[#3076]: https://github.com/rectorphp/rector/pull/3076
[#3072]: https://github.com/rectorphp/rector/pull/3072
[#3071]: https://github.com/rectorphp/rector/pull/3071
[#3070]: https://github.com/rectorphp/rector/pull/3070
[#3069]: https://github.com/rectorphp/rector/pull/3069
[#3068]: https://github.com/rectorphp/rector/pull/3068
[#3066]: https://github.com/rectorphp/rector/pull/3066
[#3065]: https://github.com/rectorphp/rector/pull/3065
[#3064]: https://github.com/rectorphp/rector/pull/3064
[#3063]: https://github.com/rectorphp/rector/pull/3063
[#3062]: https://github.com/rectorphp/rector/pull/3062
[#3059]: https://github.com/rectorphp/rector/pull/3059
[#3058]: https://github.com/rectorphp/rector/pull/3058
[#3057]: https://github.com/rectorphp/rector/pull/3057
[#3056]: https://github.com/rectorphp/rector/pull/3056
[#3054]: https://github.com/rectorphp/rector/pull/3054
[#3053]: https://github.com/rectorphp/rector/pull/3053
[#3052]: https://github.com/rectorphp/rector/pull/3052
[#3051]: https://github.com/rectorphp/rector/pull/3051
[#3050]: https://github.com/rectorphp/rector/pull/3050
[#3049]: https://github.com/rectorphp/rector/pull/3049
[#3047]: https://github.com/rectorphp/rector/pull/3047
[#3040]: https://github.com/rectorphp/rector/pull/3040
[#3039]: https://github.com/rectorphp/rector/pull/3039
[#3036]: https://github.com/rectorphp/rector/pull/3036
[#3035]: https://github.com/rectorphp/rector/pull/3035
[#3034]: https://github.com/rectorphp/rector/pull/3034
[#3032]: https://github.com/rectorphp/rector/pull/3032
[#3031]: https://github.com/rectorphp/rector/pull/3031
[#3030]: https://github.com/rectorphp/rector/pull/3030
[#3029]: https://github.com/rectorphp/rector/pull/3029
[#3027]: https://github.com/rectorphp/rector/pull/3027
[#3024]: https://github.com/rectorphp/rector/pull/3024
[#3023]: https://github.com/rectorphp/rector/pull/3023
[#3022]: https://github.com/rectorphp/rector/pull/3022
[#3021]: https://github.com/rectorphp/rector/pull/3021
[#3019]: https://github.com/rectorphp/rector/pull/3019
[#3016]: https://github.com/rectorphp/rector/pull/3016
[#3015]: https://github.com/rectorphp/rector/pull/3015
[#3013]: https://github.com/rectorphp/rector/pull/3013
[v0.7.7]: https://github.com/rectorphp/rector/compare/v0.7.4...v0.7.7
[v0.7.4]: https://github.com/rectorphp/rector/compare/v0.7.3...v0.7.4
[@pgrimaud]: https://github.com/pgrimaud
[@nightlinus]: https://github.com/nightlinus
[@greg0ire]: https://github.com/greg0ire
[@derflocki]: https://github.com/derflocki
[@crishoj]: https://github.com/crishoj
[@alexeyshockov]: https://github.com/alexeyshockov
[@Route]: https://github.com/Route
[#3217]: https://github.com/rectorphp/rector/pull/3217
[#3216]: https://github.com/rectorphp/rector/pull/3216
[#3215]: https://github.com/rectorphp/rector/pull/3215
[#3211]: https://github.com/rectorphp/rector/pull/3211
[#3204]: https://github.com/rectorphp/rector/pull/3204
[#3200]: https://github.com/rectorphp/rector/pull/3200
[#3199]: https://github.com/rectorphp/rector/pull/3199
[#3198]: https://github.com/rectorphp/rector/pull/3198
[#3197]: https://github.com/rectorphp/rector/pull/3197
[#3196]: https://github.com/rectorphp/rector/pull/3196
[#3195]: https://github.com/rectorphp/rector/pull/3195
[#3194]: https://github.com/rectorphp/rector/pull/3194
[#3193]: https://github.com/rectorphp/rector/pull/3193
[#3191]: https://github.com/rectorphp/rector/pull/3191
[#3187]: https://github.com/rectorphp/rector/pull/3187
[#3186]: https://github.com/rectorphp/rector/pull/3186
[#3182]: https://github.com/rectorphp/rector/pull/3182
[#3177]: https://github.com/rectorphp/rector/pull/3177
[#3176]: https://github.com/rectorphp/rector/pull/3176
[#3175]: https://github.com/rectorphp/rector/pull/3175
[#3174]: https://github.com/rectorphp/rector/pull/3174
[#3172]: https://github.com/rectorphp/rector/pull/3172
[#3171]: https://github.com/rectorphp/rector/pull/3171
[#3168]: https://github.com/rectorphp/rector/pull/3168
[#3164]: https://github.com/rectorphp/rector/pull/3164
[#3161]: https://github.com/rectorphp/rector/pull/3161
[#3156]: https://github.com/rectorphp/rector/pull/3156
[#3155]: https://github.com/rectorphp/rector/pull/3155
[#3154]: https://github.com/rectorphp/rector/pull/3154
[#3153]: https://github.com/rectorphp/rector/pull/3153
[#3152]: https://github.com/rectorphp/rector/pull/3152
[#3151]: https://github.com/rectorphp/rector/pull/3151
[#3149]: https://github.com/rectorphp/rector/pull/3149
[#3148]: https://github.com/rectorphp/rector/pull/3148
[#3146]: https://github.com/rectorphp/rector/pull/3146
[#3141]: https://github.com/rectorphp/rector/pull/3141
[#3140]: https://github.com/rectorphp/rector/pull/3140
[#3139]: https://github.com/rectorphp/rector/pull/3139
[#3137]: https://github.com/rectorphp/rector/pull/3137
[#3136]: https://github.com/rectorphp/rector/pull/3136
[#3134]: https://github.com/rectorphp/rector/pull/3134
[#3132]: https://github.com/rectorphp/rector/pull/3132
[#3130]: https://github.com/rectorphp/rector/pull/3130
[#3129]: https://github.com/rectorphp/rector/pull/3129
[#3128]: https://github.com/rectorphp/rector/pull/3128
[#3122]: https://github.com/rectorphp/rector/pull/3122
[#3120]: https://github.com/rectorphp/rector/pull/3120
[#3118]: https://github.com/rectorphp/rector/pull/3118
[#3117]: https://github.com/rectorphp/rector/pull/3117
[#3116]: https://github.com/rectorphp/rector/pull/3116
[#3115]: https://github.com/rectorphp/rector/pull/3115
[#3114]: https://github.com/rectorphp/rector/pull/3114
[#3113]: https://github.com/rectorphp/rector/pull/3113
[#3111]: https://github.com/rectorphp/rector/pull/3111
[#3108]: https://github.com/rectorphp/rector/pull/3108
[#3106]: https://github.com/rectorphp/rector/pull/3106
[#3103]: https://github.com/rectorphp/rector/pull/3103
[#3100]: https://github.com/rectorphp/rector/pull/3100
[#3097]: https://github.com/rectorphp/rector/pull/3097
[#3096]: https://github.com/rectorphp/rector/pull/3096
[#3094]: https://github.com/rectorphp/rector/pull/3094
[#3093]: https://github.com/rectorphp/rector/pull/3093
[#3092]: https://github.com/rectorphp/rector/pull/3092
[#3089]: https://github.com/rectorphp/rector/pull/3089
[#3084]: https://github.com/rectorphp/rector/pull/3084
[v0.7.9]: https://github.com/rectorphp/rector/compare/v0.7.8...v0.7.9
[v0.7.8]: https://github.com/rectorphp/rector/compare/v0.7.7...v0.7.8
[v0.7.16]: https://github.com/rectorphp/rector/compare/v0.7.15...v0.7.16
[v0.7.15]: https://github.com/rectorphp/rector/compare/v0.7.14...v0.7.15
[v0.7.14]: https://github.com/rectorphp/rector/compare/v0.7.12...v0.7.14
[v0.7.12]: https://github.com/rectorphp/rector/compare/v0.7.11...v0.7.12
[v0.7.11]: https://github.com/rectorphp/rector/compare/v0.7.10...v0.7.11
[v0.7.10]: https://github.com/rectorphp/rector/compare/v0.7.9...v0.7.10
[@shaal]: https://github.com/shaal
[@paslandau]: https://github.com/paslandau
[@callmebob2016]: https://github.com/callmebob2016
[@acrobat]: https://github.com/acrobat
[@UFTimmy]: https://github.com/UFTimmy
[#3266]: https://github.com/rectorphp/rector/pull/3266
[#3265]: https://github.com/rectorphp/rector/pull/3265
[#3264]: https://github.com/rectorphp/rector/pull/3264
[#3262]: https://github.com/rectorphp/rector/pull/3262
[#3261]: https://github.com/rectorphp/rector/pull/3261
[#3260]: https://github.com/rectorphp/rector/pull/3260
[#3253]: https://github.com/rectorphp/rector/pull/3253
[#3252]: https://github.com/rectorphp/rector/pull/3252
[#3251]: https://github.com/rectorphp/rector/pull/3251
[#3248]: https://github.com/rectorphp/rector/pull/3248
[#3245]: https://github.com/rectorphp/rector/pull/3245
[#3244]: https://github.com/rectorphp/rector/pull/3244
[#3242]: https://github.com/rectorphp/rector/pull/3242
[#3240]: https://github.com/rectorphp/rector/pull/3240
[#3239]: https://github.com/rectorphp/rector/pull/3239
[#3238]: https://github.com/rectorphp/rector/pull/3238
[#3237]: https://github.com/rectorphp/rector/pull/3237
[#3236]: https://github.com/rectorphp/rector/pull/3236
[#3235]: https://github.com/rectorphp/rector/pull/3235
[#3233]: https://github.com/rectorphp/rector/pull/3233
[#3232]: https://github.com/rectorphp/rector/pull/3232
[#3228]: https://github.com/rectorphp/rector/pull/3228
[#3224]: https://github.com/rectorphp/rector/pull/3224
[#3219]: https://github.com/rectorphp/rector/pull/3219
[#3218]: https://github.com/rectorphp/rector/pull/3218
[#500]: https://github.com/rectorphp/rector/pull/500
[v0.7.20]: https://github.com/rectorphp/rector/compare/v0.7.19...v0.7.20
[v0.7.19]: https://github.com/rectorphp/rector/compare/v0.7.18...v0.7.19
[v0.7.18]: https://github.com/rectorphp/rector/compare/v0.7.17...v0.7.18
[v0.7.17]: https://github.com/rectorphp/rector/compare/v0.7.16...v0.7.17
[@stephanvierkant]: https://github.com/stephanvierkant
[@atompulse]: https://github.com/atompulse
[@RiKap]: https://github.com/RiKap
[#3472]: https://github.com/rectorphp/rector/pull/3472
[#3471]: https://github.com/rectorphp/rector/pull/3471
[#3470]: https://github.com/rectorphp/rector/pull/3470
[#3469]: https://github.com/rectorphp/rector/pull/3469
[#3468]: https://github.com/rectorphp/rector/pull/3468
[#3467]: https://github.com/rectorphp/rector/pull/3467
[#3466]: https://github.com/rectorphp/rector/pull/3466
[#3459]: https://github.com/rectorphp/rector/pull/3459
[#3458]: https://github.com/rectorphp/rector/pull/3458
[#3457]: https://github.com/rectorphp/rector/pull/3457
[#3456]: https://github.com/rectorphp/rector/pull/3456
[#3455]: https://github.com/rectorphp/rector/pull/3455
[#3453]: https://github.com/rectorphp/rector/pull/3453
[#3452]: https://github.com/rectorphp/rector/pull/3452
[#3451]: https://github.com/rectorphp/rector/pull/3451
[#3450]: https://github.com/rectorphp/rector/pull/3450
[#3449]: https://github.com/rectorphp/rector/pull/3449
[#3447]: https://github.com/rectorphp/rector/pull/3447
[#3446]: https://github.com/rectorphp/rector/pull/3446
[#3445]: https://github.com/rectorphp/rector/pull/3445
[#3443]: https://github.com/rectorphp/rector/pull/3443
[#3442]: https://github.com/rectorphp/rector/pull/3442
[#3441]: https://github.com/rectorphp/rector/pull/3441
[#3440]: https://github.com/rectorphp/rector/pull/3440
[#3439]: https://github.com/rectorphp/rector/pull/3439
[#3437]: https://github.com/rectorphp/rector/pull/3437
[#3435]: https://github.com/rectorphp/rector/pull/3435
[#3434]: https://github.com/rectorphp/rector/pull/3434
[#3429]: https://github.com/rectorphp/rector/pull/3429
[#3428]: https://github.com/rectorphp/rector/pull/3428
[#3427]: https://github.com/rectorphp/rector/pull/3427
[#3423]: https://github.com/rectorphp/rector/pull/3423
[#3422]: https://github.com/rectorphp/rector/pull/3422
[#3421]: https://github.com/rectorphp/rector/pull/3421
[#3419]: https://github.com/rectorphp/rector/pull/3419
[#3418]: https://github.com/rectorphp/rector/pull/3418
[#3417]: https://github.com/rectorphp/rector/pull/3417
[#3416]: https://github.com/rectorphp/rector/pull/3416
[#3415]: https://github.com/rectorphp/rector/pull/3415
[#3412]: https://github.com/rectorphp/rector/pull/3412
[#3411]: https://github.com/rectorphp/rector/pull/3411
[#3410]: https://github.com/rectorphp/rector/pull/3410
[#3407]: https://github.com/rectorphp/rector/pull/3407
[#3406]: https://github.com/rectorphp/rector/pull/3406
[#3403]: https://github.com/rectorphp/rector/pull/3403
[#3400]: https://github.com/rectorphp/rector/pull/3400
[#3399]: https://github.com/rectorphp/rector/pull/3399
[#3398]: https://github.com/rectorphp/rector/pull/3398
[#3396]: https://github.com/rectorphp/rector/pull/3396
[#3395]: https://github.com/rectorphp/rector/pull/3395
[#3393]: https://github.com/rectorphp/rector/pull/3393
[#3392]: https://github.com/rectorphp/rector/pull/3392
[#3390]: https://github.com/rectorphp/rector/pull/3390
[#3387]: https://github.com/rectorphp/rector/pull/3387
[#3386]: https://github.com/rectorphp/rector/pull/3386
[#3379]: https://github.com/rectorphp/rector/pull/3379
[#3378]: https://github.com/rectorphp/rector/pull/3378
[#3377]: https://github.com/rectorphp/rector/pull/3377
[#3376]: https://github.com/rectorphp/rector/pull/3376
[#3372]: https://github.com/rectorphp/rector/pull/3372
[#3369]: https://github.com/rectorphp/rector/pull/3369
[#3367]: https://github.com/rectorphp/rector/pull/3367
[#3366]: https://github.com/rectorphp/rector/pull/3366
[#3365]: https://github.com/rectorphp/rector/pull/3365
[#3364]: https://github.com/rectorphp/rector/pull/3364
[#3363]: https://github.com/rectorphp/rector/pull/3363
[#3362]: https://github.com/rectorphp/rector/pull/3362
[#3361]: https://github.com/rectorphp/rector/pull/3361
[#3359]: https://github.com/rectorphp/rector/pull/3359
[#3358]: https://github.com/rectorphp/rector/pull/3358
[#3356]: https://github.com/rectorphp/rector/pull/3356
[#3355]: https://github.com/rectorphp/rector/pull/3355
[#3351]: https://github.com/rectorphp/rector/pull/3351
[#3350]: https://github.com/rectorphp/rector/pull/3350
[#3348]: https://github.com/rectorphp/rector/pull/3348
[#3346]: https://github.com/rectorphp/rector/pull/3346
[#3345]: https://github.com/rectorphp/rector/pull/3345
[#3344]: https://github.com/rectorphp/rector/pull/3344
[#3343]: https://github.com/rectorphp/rector/pull/3343
[#3341]: https://github.com/rectorphp/rector/pull/3341
[#3340]: https://github.com/rectorphp/rector/pull/3340
[#3338]: https://github.com/rectorphp/rector/pull/3338
[#3332]: https://github.com/rectorphp/rector/pull/3332
[#3328]: https://github.com/rectorphp/rector/pull/3328
[#3327]: https://github.com/rectorphp/rector/pull/3327
[#3326]: https://github.com/rectorphp/rector/pull/3326
[#3325]: https://github.com/rectorphp/rector/pull/3325
[#3324]: https://github.com/rectorphp/rector/pull/3324
[#3322]: https://github.com/rectorphp/rector/pull/3322
[#3321]: https://github.com/rectorphp/rector/pull/3321
[#3320]: https://github.com/rectorphp/rector/pull/3320
[#3319]: https://github.com/rectorphp/rector/pull/3319
[#3318]: https://github.com/rectorphp/rector/pull/3318
[#3317]: https://github.com/rectorphp/rector/pull/3317
[#3314]: https://github.com/rectorphp/rector/pull/3314
[#3313]: https://github.com/rectorphp/rector/pull/3313
[#3311]: https://github.com/rectorphp/rector/pull/3311
[#3310]: https://github.com/rectorphp/rector/pull/3310
[#3309]: https://github.com/rectorphp/rector/pull/3309
[#3308]: https://github.com/rectorphp/rector/pull/3308
[#3306]: https://github.com/rectorphp/rector/pull/3306
[#3305]: https://github.com/rectorphp/rector/pull/3305
[#3304]: https://github.com/rectorphp/rector/pull/3304
[#3302]: https://github.com/rectorphp/rector/pull/3302
[#3301]: https://github.com/rectorphp/rector/pull/3301
[#3300]: https://github.com/rectorphp/rector/pull/3300
[#3299]: https://github.com/rectorphp/rector/pull/3299
[#3298]: https://github.com/rectorphp/rector/pull/3298
[#3297]: https://github.com/rectorphp/rector/pull/3297
[#3296]: https://github.com/rectorphp/rector/pull/3296
[#3295]: https://github.com/rectorphp/rector/pull/3295
[#3294]: https://github.com/rectorphp/rector/pull/3294
[#3293]: https://github.com/rectorphp/rector/pull/3293
[#3290]: https://github.com/rectorphp/rector/pull/3290
[#3289]: https://github.com/rectorphp/rector/pull/3289
[#3288]: https://github.com/rectorphp/rector/pull/3288
[#3287]: https://github.com/rectorphp/rector/pull/3287
[#3285]: https://github.com/rectorphp/rector/pull/3285
[#3284]: https://github.com/rectorphp/rector/pull/3284
[#3283]: https://github.com/rectorphp/rector/pull/3283
[#3281]: https://github.com/rectorphp/rector/pull/3281
[#3280]: https://github.com/rectorphp/rector/pull/3280
[#3279]: https://github.com/rectorphp/rector/pull/3279
[#3278]: https://github.com/rectorphp/rector/pull/3278
[#3277]: https://github.com/rectorphp/rector/pull/3277
[#3275]: https://github.com/rectorphp/rector/pull/3275
[#3273]: https://github.com/rectorphp/rector/pull/3273
[#3270]: https://github.com/rectorphp/rector/pull/3270
[#3269]: https://github.com/rectorphp/rector/pull/3269
[#3268]: https://github.com/rectorphp/rector/pull/3268
[#3254]: https://github.com/rectorphp/rector/pull/3254
[#3241]: https://github.com/rectorphp/rector/pull/3241
[#6]: https://github.com/rectorphp/rector/pull/6
[#5]: https://github.com/rectorphp/rector/pull/5
[#4]: https://github.com/rectorphp/rector/pull/4
[#2]: https://github.com/rectorphp/rector/pull/2
[v0.7.29]: https://github.com/rectorphp/rector/compare/v0.7.27...v0.7.29
[v0.7.27]: https://github.com/rectorphp/rector/compare/v0.7.26...v0.7.27
[v0.7.26]: https://github.com/rectorphp/rector/compare/v0.7.23...v0.7.26
[v0.7.23]: https://github.com/rectorphp/rector/compare/v0.7.22...v0.7.23
[v0.7.22]: https://github.com/rectorphp/rector/compare/v0.7.20...v0.7.22
[@tomasnorre]: https://github.com/tomasnorre
[@tavy315]: https://github.com/tavy315
[@norberttech]: https://github.com/norberttech
[@noRector]: https://github.com/noRector
[@mixin]: https://github.com/mixin
[@guilliamxavier]: https://github.com/guilliamxavier
[@eclipxe13]: https://github.com/eclipxe13
[@ddziaduch]: https://github.com/ddziaduch
[@bitgandtter]: https://github.com/bitgandtter
[@MetalArend]: https://github.com/MetalArend
[#3578]: https://github.com/rectorphp/rector/pull/3578
[#3577]: https://github.com/rectorphp/rector/pull/3577
[#3576]: https://github.com/rectorphp/rector/pull/3576
[#3574]: https://github.com/rectorphp/rector/pull/3574
[#3573]: https://github.com/rectorphp/rector/pull/3573
[#3572]: https://github.com/rectorphp/rector/pull/3572
[#3571]: https://github.com/rectorphp/rector/pull/3571
[#3566]: https://github.com/rectorphp/rector/pull/3566
[#3565]: https://github.com/rectorphp/rector/pull/3565
[#3563]: https://github.com/rectorphp/rector/pull/3563
[#3562]: https://github.com/rectorphp/rector/pull/3562
[#3561]: https://github.com/rectorphp/rector/pull/3561
[#3560]: https://github.com/rectorphp/rector/pull/3560
[#3559]: https://github.com/rectorphp/rector/pull/3559
[#3558]: https://github.com/rectorphp/rector/pull/3558
[#3557]: https://github.com/rectorphp/rector/pull/3557
[#3556]: https://github.com/rectorphp/rector/pull/3556
[#3555]: https://github.com/rectorphp/rector/pull/3555
[#3554]: https://github.com/rectorphp/rector/pull/3554
[#3553]: https://github.com/rectorphp/rector/pull/3553
[#3552]: https://github.com/rectorphp/rector/pull/3552
[#3551]: https://github.com/rectorphp/rector/pull/3551
[#3550]: https://github.com/rectorphp/rector/pull/3550
[#3549]: https://github.com/rectorphp/rector/pull/3549
[#3548]: https://github.com/rectorphp/rector/pull/3548
[#3547]: https://github.com/rectorphp/rector/pull/3547
[#3546]: https://github.com/rectorphp/rector/pull/3546
[#3539]: https://github.com/rectorphp/rector/pull/3539
[#3538]: https://github.com/rectorphp/rector/pull/3538
[#3536]: https://github.com/rectorphp/rector/pull/3536
[#3535]: https://github.com/rectorphp/rector/pull/3535
[#3534]: https://github.com/rectorphp/rector/pull/3534
[#3533]: https://github.com/rectorphp/rector/pull/3533
[#3532]: https://github.com/rectorphp/rector/pull/3532
[#3528]: https://github.com/rectorphp/rector/pull/3528
[#3527]: https://github.com/rectorphp/rector/pull/3527
[#3526]: https://github.com/rectorphp/rector/pull/3526
[#3525]: https://github.com/rectorphp/rector/pull/3525
[#3524]: https://github.com/rectorphp/rector/pull/3524
[#3523]: https://github.com/rectorphp/rector/pull/3523
[#3522]: https://github.com/rectorphp/rector/pull/3522
[#3521]: https://github.com/rectorphp/rector/pull/3521
[#3520]: https://github.com/rectorphp/rector/pull/3520
[#3519]: https://github.com/rectorphp/rector/pull/3519
[#3518]: https://github.com/rectorphp/rector/pull/3518
[#3512]: https://github.com/rectorphp/rector/pull/3512
[#3511]: https://github.com/rectorphp/rector/pull/3511
[#3508]: https://github.com/rectorphp/rector/pull/3508
[#3507]: https://github.com/rectorphp/rector/pull/3507
[#3506]: https://github.com/rectorphp/rector/pull/3506
[#3505]: https://github.com/rectorphp/rector/pull/3505
[#3503]: https://github.com/rectorphp/rector/pull/3503
[#3502]: https://github.com/rectorphp/rector/pull/3502
[#3499]: https://github.com/rectorphp/rector/pull/3499
[#3498]: https://github.com/rectorphp/rector/pull/3498
[#3497]: https://github.com/rectorphp/rector/pull/3497
[#3496]: https://github.com/rectorphp/rector/pull/3496
[#3495]: https://github.com/rectorphp/rector/pull/3495
[#3494]: https://github.com/rectorphp/rector/pull/3494
[#3492]: https://github.com/rectorphp/rector/pull/3492
[#3491]: https://github.com/rectorphp/rector/pull/3491
[#3488]: https://github.com/rectorphp/rector/pull/3488
[#3487]: https://github.com/rectorphp/rector/pull/3487
[#3486]: https://github.com/rectorphp/rector/pull/3486
[#3485]: https://github.com/rectorphp/rector/pull/3485
[#3484]: https://github.com/rectorphp/rector/pull/3484
[#3483]: https://github.com/rectorphp/rector/pull/3483
[#3482]: https://github.com/rectorphp/rector/pull/3482
[#3481]: https://github.com/rectorphp/rector/pull/3481
[#3479]: https://github.com/rectorphp/rector/pull/3479
[#3478]: https://github.com/rectorphp/rector/pull/3478
[#3477]: https://github.com/rectorphp/rector/pull/3477
[#3476]: https://github.com/rectorphp/rector/pull/3476
[#3475]: https://github.com/rectorphp/rector/pull/3475
[#3474]: https://github.com/rectorphp/rector/pull/3474
[#3460]: https://github.com/rectorphp/rector/pull/3460
[v0.7.37]: https://github.com/rectorphp/rector/compare/v0.7.36...v0.7.37
[v0.7.36]: https://github.com/rectorphp/rector/compare/v0.7.35...v0.7.36
[v0.7.35]: https://github.com/rectorphp/rector/compare/v0.7.34...v0.7.35
[v0.7.34]: https://github.com/rectorphp/rector/compare/v0.7.32...v0.7.34
[v0.7.32]: https://github.com/rectorphp/rector/compare/v0.7.31...v0.7.32
[v0.7.31]: https://github.com/rectorphp/rector/compare/v0.7.30...v0.7.31
[v0.7.30]: https://github.com/rectorphp/rector/compare/v0.7.29...v0.7.30
[@route]: https://github.com/route
[@property]: https://github.com/property
[@inject]: https://github.com/inject
[@garas]: https://github.com/garas
[@fixme]: https://github.com/fixme
[@derrickschoen]: https://github.com/derrickschoen
[@codereviewvideos]: https://github.com/codereviewvideos
[@berezuev]: https://github.com/berezuev
[@PurpleBooth]: https://github.com/PurpleBooth
