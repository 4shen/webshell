#!/bin/bash
#
# ownCloud
#
# @author Artur Neumann
# @copyright Copyright (c) 2017 Artur Neumann info@individual-it.net
#
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions to compare version strings
verlte() {
	[ "$1" = "`echo -e "$1\n$2" | sort -V | head -n1`" ]
}

verlt() {
	[ "$1" = "$2" ] && return 1 || verlte $1 $2
}

# @param $1 admin authentication string username:password
# @param $2 occ url
# @param $3 command
# sets $REMOTE_OCC_STDOUT and $REMOTE_OCC_STDERR from returned xml date
# @return occ return code given in the xml data
remote_occ() {
	RESULT=`curl -s -u $1 $2 -d "command=$3"`
	RETURN=`echo ${RESULT} | xmllint --xpath "string(ocs/data/code)" - | sed 's/ //g'`
	# we could not find a proper return of the testing app, so something went wrong
	if [ -z "${RETURN}" ]
	then
		RETURN=1
		REMOTE_OCC_STDERR=${RESULT}
	else
		REMOTE_OCC_STDOUT=`echo ${RESULT} | xmllint --xpath "string(ocs/data/stdOut)" - | sed 's/ //g'`
		REMOTE_OCC_STDERR=`echo ${RESULT} | xmllint --xpath "string(ocs/data/stdErr)" - | sed 's/ //g'`
	fi
	return ${RETURN}
}

# save the current language and set the language to "C"
# we want to have it all in english to be able to parse outputs
OLD_LANG=${LANG}
export LANG=C

OCC=./occ

BASE_URL="http://${SRV_HOST_NAME}"
if [ ! -z "${SRV_HOST_PORT}" ] && [ "${SRV_HOST_PORT}" != "80" ]
then
	BASE_URL="${BASE_URL}:${SRV_HOST_PORT}"
fi

IPV4_URL="${BASE_URL}"
IPV6_URL="${BASE_URL}"

if [ -n "${SRV_HOST_URL}" ]
then
	BASE_URL="${BASE_URL}/${SRV_HOST_URL}"
	IPV4_URL="${IPV4_URL}/${SRV_HOST_URL}"
	IPV6_URL="${IPV6_URL}/${SRV_HOST_URL}"
fi

REMOTE_FED_BASE_URL="http://${REMOTE_FED_SRV_HOST_NAME}"

if [ ! -z "${REMOTE_FED_SRV_HOST_PORT}" ] && [ "${REMOTE_FED_SRV_HOST_PORT}" != "80" ]
then
	REMOTE_FED_BASE_URL="${REMOTE_FED_BASE_URL}:${REMOTE_FED_SRV_HOST_PORT}"
fi

if [ ! -z "${IPV4_HOST_NAME}" ]
then
	IPV4_URL="http://${IPV4_HOST_NAME}"
	if [ ! -z "${SRV_HOST_PORT}" ] && [ "${SRV_HOST_PORT}" != "80" ]
	then
		IPV4_URL="${IPV4_URL}:${SRV_HOST_PORT}"
	fi
fi

if [ ! -z "${IPV6_HOST_NAME}" ]
then
	IPV6_URL="http://${IPV6_HOST_NAME}"
	if [ ! -z "${SRV_HOST_PORT}" ] && [ "${SRV_HOST_PORT}" != "80" ]
	then
		IPV6_URL="${IPV6_URL}:${SRV_HOST_PORT}"
	fi
fi

if [ -n "${REMOTE_FED_SRV_HOST_URL}" ]
then
	REMOTE_FED_BASE_URL="${REMOTE_FED_BASE_URL}/${REMOTE_FED_SRV_HOST_URL}"
fi

OCC_URL="${BASE_URL}/ocs/v2.php/apps/testing/api/v1/occ"

# Provide a default admin username and password.
# But let the caller pass them if they wish
if [ -z "${ADMIN_USERNAME}" ]
then
	ADMIN_USERNAME="admin"
fi

if [ -z "${ADMIN_PASSWORD}" ]
then
	ADMIN_PASSWORD="admin"
fi

ADMIN_AUTH="${ADMIN_USERNAME}:${ADMIN_PASSWORD}"

if [ -z "${APPS_TO_DISABLE}" ]
then
	APPS_TO_DISABLE="firstrunwizard notifications"
fi

if [ -z "${APPS_TO_ENABLE}" ]
then
	APPS_TO_ENABLE=""
fi

# Look for command line options for:
# -c or --config - specify a behat.yml to use
# --feature - specify a single feature to run
# --suite - specify a single suite to run
# --tags - specify tags for scenarios to run (or not)
# --remote - the server under test is remote, so we cannot locally enable the
#            testing app. We have to assume it is already enabled.
# --all-suites - run all Behat test suites
# --norerun - do not rerun failed webUI scenarios
BEHAT_TAGS_OPTION_FOUND=false
REMOTE_ONLY=false
ALL_SUITES=false
RERUN_FAILED_WEBUI_SCENARIOS=true

while [[ $# -gt 0 ]]
do
	key="$1"
	case ${key} in
		-c|--config)
			BEHAT_YML="$2"
			shift
			;;
		--feature)
			BEHAT_FEATURE="$2"
			shift
			;;
		--suite)
			BEHAT_SUITE="$2"
			shift
			;;
		--tags)
			BEHAT_TAGS="$2"
			BEHAT_TAGS_OPTION_FOUND=true
			shift
			;;
		--remote)
			REMOTE_ONLY=true
			;;
		--all-suites)
			ALL_SUITES=true
			;;
		--norerun)
			RERUN_FAILED_WEBUI_SCENARIOS=false
			;;
		*)
			# ignore unknown options
			;;
	esac
	shift
done

# An odd parameter by itself at the end is a feature to run
if [ -n "$1" ]
then
	BEHAT_FEATURE="$1"
fi

# If a feature file has been specified but no suite, then deduce the suite
if [ -n "${BEHAT_FEATURE}" ] && [ -z "${BEHAT_SUITE}" ]
then
    FEATURE_PATH=`dirname ${BEHAT_FEATURE}`
    BEHAT_SUITE=`basename ${FEATURE_PATH}`
fi

if [ -z "${BEHAT_YML}" ]
then
	BEHAT_YML="tests/acceptance/config/behat.yml"
fi

if [ -z "${BEHAT_SUITE}" ] && [ "${ALL_SUITES}" = false ]
then
	echo "ERROR: webUI tests must be run by suite."
	echo "No suite specified. Specify a suite by either:"
	echo "  setting the BEHAT_SUITE env variable"
	echo "  using the --suite parameter"
	echo "To run all webUI suites in a single run, specify"
	echo "  --all-suites"
	echo "Running all webUI suites in a single run will take significant time"
	exit 1
else
	if [ "${ALL_SUITES}" = true ]
	then
		BEHAT_SUITE_OPTION=""
	else
		BEHAT_SUITE_OPTION="--suite=${BEHAT_SUITE}"
	fi
fi

BEHAT_TAG_OPTION="--tags"

if [ -z "${MAILHOG_HOST}" ]
then
	MAILHOG_HOST="127.0.0.1"
fi
if [ -z "${MAILHOG_SMTP_PORT}" ]
then
	MAILHOG_SMTP_PORT="1025"
fi

# check if we can rely on a local ./occ command or if we are testing
# a remote instance (e.g. inside docker).
# if we have a remote instance we cannot enable the testing app and
# we have to hope it is enabled by other ways
if [ "${REMOTE_ONLY}" = false ]
then
	# enable testing app
	PREVIOUS_TESTING_APP_STATUS=$(${OCC} --no-warnings app:list "^testing$")
	if [[ "${PREVIOUS_TESTING_APP_STATUS}" =~ ^Disabled: ]]
	then
		${OCC} app:enable testing
		TESTING_ENABLED_BY_SCRIPT=true;
	else
		TESTING_ENABLED_BY_SCRIPT=false;
	fi
else
	TESTING_ENABLED_BY_SCRIPT=false;
fi

# set SMTP settings
remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings config:system:get mail_domain"
PREVIOUS_MAIL_DOMAIN=${REMOTE_OCC_STDOUT}
remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings config:system:get mail_from_address"
PREVIOUS_MAIL_FROM_ADDRESS=${REMOTE_OCC_STDOUT}
remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings config:system:get mail_smtpmode"
PREVIOUS_MAIL_SMTP_MODE=${REMOTE_OCC_STDOUT}
remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings config:system:get mail_smtphost"
PREVIOUS_MAIL_SMTP_HOST=${REMOTE_OCC_STDOUT}
remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings config:system:get mail_smtpport"
PREVIOUS_MAIL_SMTP_PORT=${REMOTE_OCC_STDOUT}

remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_domain --value=foobar.com"
remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_from_address --value=owncloud"
remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_smtpmode --value=smtp"
remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_smtphost --value=${MAILHOG_HOST}"
remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_smtpport --value=${MAILHOG_SMTP_PORT}"

# get the current backgroundjobs_mode
remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:app:get core backgroundjobs_mode"
PREVIOUS_BACKGROUNDJOBS_MODE=${REMOTE_OCC_STDOUT}
# switch to webcron
remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:app:set core backgroundjobs_mode --value webcron"
if [ $? -ne 0 ]
then
	echo "WARNING: Could not set backgroundjobs mode to 'webcron'"
fi

APPS_TO_REENABLE="";

for APP_TO_DISABLE in ${APPS_TO_DISABLE}; do
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings app:list ^${APP_TO_DISABLE}$"
	PREVIOUS_APP_STATUS=${REMOTE_OCC_STDOUT}
	if [[ "${PREVIOUS_APP_STATUS}" =~ ^Enabled: ]]
	then
		APPS_TO_REENABLE="${APPS_TO_REENABLE} ${APP_TO_DISABLE}";
		remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings app:disable ${APP_TO_DISABLE}"
	fi
done

APPS_TO_REDISABLE="";

for APP_TO_ENABLE in ${APPS_TO_ENABLE}; do
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings app:list ^${APP_TO_ENABLE}$"
	PREVIOUS_APP_STATUS=${REMOTE_OCC_STDOUT}
	if [[ "${PREVIOUS_APP_STATUS}" =~ ^Disabled: ]]
	then
		APPS_TO_REDISABLE="${APPS_TO_REDISABLE} ${APP_TO_ENABLE}";
		remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings app:enable ${APP_TO_ENABLE}"
	fi
done

# we need to skip some tests in certain browsers
# and also skip tests if tags were given in the call of this script
if [ "${BROWSER}" == "internet explorer" ] || [ "${BROWSER}" == "MicrosoftEdge" ] || [ "${BROWSER}" == "firefox" ]
then
	BROWSER_IN_CAPITALS=${BROWSER//[[:blank:]]/}
	BROWSER_IN_CAPITALS=${BROWSER_IN_CAPITALS^^}
	
	if [ "${BEHAT_TAGS_OPTION_FOUND}" = true ]
	then
		if [ -z "${BEHAT_TAGS}" ]
		then
			BEHAT_TAGS='~@skipOn'${BROWSER_IN_CAPITALS}
		else
			BEHAT_TAGS="${BEHAT_TAGS}&&~@skipOn"${BROWSER_IN_CAPITALS}
		fi
	else
		BEHAT_TAGS='~@skip&&~@skipOn'${BROWSER_IN_CAPITALS}
	fi
else
	if [ "${BEHAT_TAGS_OPTION_FOUND}" = true ]
	then
		if [ -z "${BEHAT_TAGS}" ]
		then
			BEHAT_TAG_OPTION=""
		fi
	else
		BEHAT_TAGS='~@skip'
	fi
fi

# skip tests tagged with the current oC version
# one, two or three parts of the version can be used
# e.g.
# @skipOnOcV10.0.4
# @skipOnOcV10.0
# @skipOnOcV10

remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:get version"
OWNCLOUD_VERSION=`echo ${REMOTE_OCC_STDOUT} | cut -d"." -f1-3`
BEHAT_TAGS='~@skipOnOcV'${OWNCLOUD_VERSION}'&&'${BEHAT_TAGS}
OWNCLOUD_VERSION=`echo ${OWNCLOUD_VERSION} | cut -d"." -f1-2`
BEHAT_TAGS='~@skipOnOcV'${OWNCLOUD_VERSION}'&&'${BEHAT_TAGS}
OWNCLOUD_VERSION=`echo ${OWNCLOUD_VERSION} | cut -d"." -f1`
BEHAT_TAGS='~@skipOnOcV'${OWNCLOUD_VERSION}'&&'${BEHAT_TAGS}

# if we running remote only tests add an other skip '@skipWhenTestingRemoteSystems'
if [ "${REMOTE_ONLY}" = true ]
then
	BEHAT_TAGS='~@skipWhenTestingRemoteSystems&&'${BEHAT_TAGS}
fi

BEHAT_TAGS='@webUI&&'${BEHAT_TAGS}

if [ "${BROWSER}" == "firefox" ]
then
	# set screen resolution so that hopefully dragable elements will be visible
	# FF gives problems if the destination element is not visible
	EXTRA_CAPABILITIES='"screenResolution":"1920x1080",'

	# this selenium version works for Firefox after V47
	# we no longer need to support testing of Firefox V47 or earlier
	EXTRA_CAPABILITIES='"seleniumVersion":"3.4.0",'${EXTRA_CAPABILITIES}
fi

if [ "${BROWSER}" == "internet explorer" ]
then
	EXTRA_CAPABILITIES='"iedriverVersion": "3.4.0","requiresWindowFocus":true,"screenResolution":"1920x1080",'
fi

EXTRA_CAPABILITIES=${EXTRA_CAPABILITIES}'"browserVersion":"'${BROWSER_VERSION}'","maxDuration":"3600"'

# Set up personalized skeleton
remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings config:system:get skeletondirectory"

PREVIOUS_SKELETON_DIR=${REMOTE_OCC_STDOUT}

# $SRC_SKELETON_DIR is the path to the skeleton folder on the machine where the tests are executed
# it is used for file comparisons in various tests
export SRC_SKELETON_DIR=$(pwd)/apps/testing/data/webUISkeleton
# $SKELETON_DIR is the path to the skeleton folder on the machine where oC runs (system under test)
# it is used to give users a defined set of files and folders for the tests
if [ -z "${SKELETON_DIR}" ]
then
	export SKELETON_DIR="${SRC_SKELETON_DIR}"
fi

remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set skeletondirectory --value=${SKELETON_DIR}"
if [ $? -ne 0 ]
then
	echo -e "Could not set skeleton directory. Result:\n'${REMOTE_OCC_STDERR}'"
	exit 1
fi

TEST_LOG_FILE=$(mktemp)

if [ -z "${SELENIUM_HOST}" ]
then
	SELENIUM_HOST=localhost
fi

if [ -z "${SELENIUM_PORT}" ]
then
	SELENIUM_PORT=4445
fi

if [ "${ALL_SUITES}" = true ]
then
	SUITE_FEATURE_TEXT="all"
else
	SUITE_FEATURE_TEXT="${BEHAT_SUITE}"
fi

if [ -n "${BEHAT_FEATURE}" ]
then
    # If running a whole feature, it will be something like login.feature
    # If running just a single scenario, it will also have the line number
    # like login.feature:36 - which will be parsed correctly like a "file"
    # by basename.
    BEHAT_FEATURE_FILE=`basename ${BEHAT_FEATURE}`
    SUITE_FEATURE_TEXT="${SUITE_FEATURE_TEXT} ${BEHAT_FEATURE_FILE}"
fi

echo "Running ${SUITE_FEATURE_TEXT} tests on '${BROWSER}' (${BROWSER_VERSION}) on ${PLATFORM}" | tee ${TEST_LOG_FILE}
export BEHAT_PARAMS='{"extensions" : {"Behat\\MinkExtension" : {"browser_name": "'${BROWSER}'", "base_url" : "'${BASE_URL}'", "selenium2":{"capabilities": {"marionette":null, "browser": "'${BROWSER}'", "version": "'${BROWSER_VERSION}'", "platform": "'${PLATFORM}'", "name": "'${TRAVIS_REPO_SLUG}' - '${TRAVIS_JOB_NUMBER}'", "extra_capabilities": {'${EXTRA_CAPABILITIES}'}}, "wd_host":"http://'${SAUCE_USERNAME}:${SAUCE_ACCESS_KEY}'@'${SELENIUM_HOST}':'${SELENIUM_PORT}'/wd/hub"}}, "SensioLabs\\Behat\\PageObjectExtension" : {}}}'
export IPV4_URL
export IPV6_URL
export REMOTE_FED_BASE_URL
export FILES_FOR_UPLOAD="$(pwd)/tests/acceptance/filesForUpload/"

# Provide TEST_SERVER* env vars. Some API acceptance test code uses these.
export TEST_SERVER_URL="${BASE_URL}"
export TEST_SERVER_FED_URL="${REMOTE_FED_BASE_URL}"

if [ ! -w ${FILES_FOR_UPLOAD} ]
then
	echo "WARNING: cannot write to upload folder '${FILES_FOR_UPLOAD}', some upload tests might fail"
fi

lib/composer/bin/behat --strict -c ${BEHAT_YML} ${BEHAT_SUITE_OPTION} ${BEHAT_TAG_OPTION} ${BEHAT_TAGS} ${BEHAT_FEATURE} -v  2>&1 | tee -a ${TEST_LOG_FILE}

BEHAT_EXIT_STATUS=${PIPESTATUS[0]}

if [ ${BEHAT_EXIT_STATUS} -eq 0 ]
then
	PASSED=true
else
	PASSED=false
fi

if [ "${PASSED}" = false ] && [ "${RERUN_FAILED_WEBUI_SCENARIOS}" = true ]
then
	echo test run failed with exit status: ${BEHAT_EXIT_STATUS}
	PASSED=true
	SOME_SCENARIO_RERUN=false
	FAILED_SCENARIOS=`awk '/Failed scenarios:/',0 ${TEST_LOG_FILE} | grep feature`
	for FEATURE in ${FAILED_SCENARIOS}
		do
			SOME_SCENARIO_RERUN=true
			echo rerun failed tests: ${FEATURE}
			lib/composer/bin/behat --strict -c ${BEHAT_YML} ${BEHAT_SUITE_OPTION} ${BEHAT_TAG_OPTION} ${BEHAT_TAGS} ${FEATURE} -v  2>&1 | tee -a ${TEST_LOG_FILE}
			BEHAT_EXIT_STATUS=${PIPESTATUS[0]}
			if [ ${BEHAT_EXIT_STATUS} -ne 0 ]
			then
				echo test rerun failed with exit status: ${BEHAT_EXIT_STATUS}
				PASSED=false
			fi
		done

	if [ "${SOME_SCENARIO_RERUN}" = false ]
	then
		# If the original Behat had a fatal PHP error and exited directly with
		# a "bad" exit code, then it may not have even logged a summary of the
		# failed scenarios. In that case there was an error and no scenarios
		# have been rerun. So PASSED needs to be false.
		PASSED=false
	fi
fi

if [ "${BEHAT_TAGS_OPTION_FOUND}" != true ]
then
	# The behat run above specified to skip scenarios tagged @skip
	# Report them in a dry-run so they can be seen
	# Big red error output is displayed if there are no matching scenarios - send it to null
	DRY_RUN_FILE=$(mktemp)
	lib/composer/bin/behat --dry-run --colors -c ${BEHAT_YML} --tags '@webUI&&@skip' ${BEHAT_FEATURE} 1>${DRY_RUN_FILE} 2>/dev/null
	if grep -q -m 1 'No scenarios' "${DRY_RUN_FILE}"
	then
		# If there are no skip scenarios, then no need to report that
		:
	else
		echo ""
		echo "The following tests were skipped because they are tagged @skip:"
		cat "${DRY_RUN_FILE}" | tee -a ${TEST_LOG_FILE}
	fi
	rm -f "${DRY_RUN_FILE}"
fi

# Put back personalized skeleton
if [ "A${PREVIOUS_SKELETON_DIR}" = "A" ]
then
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:delete skeletondirectory"
else
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set skeletondirectory --value=${PREVIOUS_SKELETON_DIR}"
fi

# Put back smtp settings
if [ "A${PREVIOUS_MAIL_DOMAIN}" = "A" ]
then
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:delete mail_domain"
else
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_domain --value=${PREVIOUS_MAIL_DOMAIN}"
fi

if [ "A${PREVIOUS_MAIL_FROM_ADDRESS}" = "A" ]
then
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:delete mail_from_address"
else
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_from_address --value=${PREVIOUS_MAIL_FROM_ADDRESS}"
fi

if [ "A${PREVIOUS_MAIL_SMTP_MODE}" = "A" ]
then
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:delete mail_smtpmode"
else
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_smtpmode --value=${PREVIOUS_MAIL_SMTP_MODE}"
fi

if [ "A${PREVIOUS_MAIL_SMTP_HOST}" = "A" ]
then
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:delete mail_smtphost"
else
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_smtphost --value=${PREVIOUS_MAIL_SMTP_HOST}"
fi

if [ "A${PREVIOUS_MAIL_SMTP_PORT}" = "A" ]
then
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:delete mail_smtpport"
else
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:system:set mail_smtpport --value=${PREVIOUS_MAIL_SMTP_PORT}"
fi

for APP_TO_ENABLE in ${APPS_TO_REENABLE}; do
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings app:enable ${APP_TO_ENABLE}"
done

for APP_TO_DISABLE in ${APPS_TO_REDISABLE}; do
	remote_occ ${ADMIN_AUTH} ${OCC_URL} "--no-warnings app:disable ${APP_TO_DISABLE}"
done

# put back the backgroundjobs_mode
remote_occ ${ADMIN_AUTH} ${OCC_URL} "config:app:set core backgroundjobs_mode --value ${PREVIOUS_BACKGROUNDJOBS_MODE}"

# Put back state of the testing app
if [ "${TESTING_ENABLED_BY_SCRIPT}" = true ]
then
	${OCC} app:disable testing
fi

#upload log file for later analysis
if [ "${PASSED}" = false ] && [ ! -z "${REPORTING_WEBDAV_USER}" ] && [ ! -z "${REPORTING_WEBDAV_PWD}" ] && [ ! -z "${REPORTING_WEBDAV_URL}" ]
then
	curl -u ${REPORTING_WEBDAV_USER}:${REPORTING_WEBDAV_PWD} -T ${TEST_LOG_FILE} ${REPORTING_WEBDAV_URL}/"${TRAVIS_JOB_NUMBER}"_`date "+%F_%T"`.log
fi

#reset the original language
export LANG=${OLD_LANG}

rm -f "${TEST_LOG_FILE}"

if [ "${PASSED}" = true ]
then
	exit 0
else
	exit 1
fi