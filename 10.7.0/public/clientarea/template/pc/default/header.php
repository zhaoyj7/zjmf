<!DOCTYPE html>
<html lang="en" theme="{$clientarea_theme_color}" id="addons_js" addons_js='{:json_encode($addons)}'>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title></title>
    <link rel="icon" href="/favicon.ico">
    <!-- 公共 -->
    <script>
        const url = "/{$template_catalog}/template/{$public_themes}/"
        const system_version = "{$system_version}"
    </script>

    <!-- 模板样式 -->
    <link rel="stylesheet" href="/{$template_catalog}/template/{$public_themes}/css/common/reset.css">

    <script src="/{$template_catalog}/template/{$themes}/theme/index.js"></script>

    <link rel="stylesheet" href="/{$template_catalog}/template/{$public_themes}/css/common/common.css">
    <link rel="stylesheet" href="/upload/common/iconfont/iconfont.css">


    <script src="/{$template_catalog}/template/{$public_themes}/js/common/vue.js"></script>
    <script src="/{$template_catalog}/template/{$public_themes}/js/common/element.js"></script>


    <script src="/{$template_catalog}/template/{$public_themes}/utils/util.js"></script>
    <script src="/{$template_catalog}/template/{$public_themes}/lang/index.js"></script>
    <script src="/{$template_catalog}/template/{$public_themes}/js/common/common.js"></script>
