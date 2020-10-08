Base Plumrocket extension for M2

### System config

**Layout handles**

Extension adding two handles to each plumrocket configuration.
1. `pr_system_config_edit`
2. `pr_system_config_edit + section_id` e.g - pr_system_config_edit_pr_cookie

**Text editor (wysiwyg)** 

frontend_model `Block\Adminhtml\System\Config\Form\Editor`

Exist possibility modify wysiwyg config by adding attributes

```xml
<field id="notice_text" translate="label" type="editor" sortOrder="60" showInDefault="1" showInWebsite="1" showInStore="1">
    <label>Notice Text</label>
    <frontend_model>Plumrocket\Base\Block\Adminhtml\System\Config\Form\Editor</frontend_model>
    <config_path>prgdpr/cookie_consent/notice_text</config_path>
    <attribute type="pr_editor_height">300px</attribute>
</field>
```

**Color Picker**

frontend_model `Plumrocket\Base\Block\Adminhtml\System\Config\Form\ColorPicker`

```xml
<field id="background_color" translate="label" type="text" sortOrder="30" showInDefault="1" showInWebsite="1" showInStore="1">
    <label>Background Color</label>
    <frontend_model>Plumrocket\Base\Block\Adminhtml\System\Config\Form\ColorPicker</frontend_model>
</field>
```

### Abstract Classes:

`Helper\AbstractConfig` - for creating config helpers
`Model\System\Config\AbstractSource` - for creating system config sources
`Setup\AbstractUninstall` - for creating uninstall scripts;
