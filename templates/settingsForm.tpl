{**
 * plugins/generic/VOJSgoogleAnalytics/settingsForm.tpl
 *
 * Google Analytics plugin settings
 *
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#gaSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="gaSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="gaSettingsFormNotification"}

	<div id="description">{translate key="plugins.generic.VOJSgoogleAnalytics.manager.settings.description"}</div>

	{fbvFormArea id="webFeedSettingsFormArea"}
		{fbvElement type="text" id="googleAnalyticsSiteId" value=$googleAnalyticsSiteId label="plugins.generic.VOJSgoogleAnalytics.manager.settings.googleAnalyticsSiteId"}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
