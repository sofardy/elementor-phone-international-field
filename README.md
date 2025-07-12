# Elementor Phone International By Sofard

Professional phone field for Elementor Forms with international country selection, input masking, and validation.

## ✨ Features

- 🌍 **International country selection** with flags
- 🎭 **Smart input masking** based on selected country
- ✅ **Real-time validation** with visual feedback
- 📱 **Mobile-friendly** responsive design
- 🔄 **Automatic updates** via GitHub
- 🎨 **Customizable** preferred countries and initial country
- 💾 **Full phone number storage** in submissions

## 🚀 Installation

1. Download the latest release from [Releases page](https://github.com/your-username/elementor-phone-field/releases)
2. Upload the plugin ZIP file through WordPress admin or extract to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. The new "Phone with Country" field will appear in Elementor Forms

## ⚙️ Setup for Auto-Updates

1. Fork this repository or create your own
2. Edit `elementor-phone-field.php` and replace:
   - `your-username` with your GitHub username
   - `elementor-phone-field` with your repository name
3. Create releases with tags like `v1.0.1`, `v1.0.2`, etc.
4. The plugin will automatically check for updates every 12 hours

## 🔧 Configuration

In Elementor Form builder, select "Phone with Country" field type and configure:

- **Preferred Countries**: Comma-separated country codes (e.g., `ua,us,gb`)
- **Initial Country**: Default country code (e.g., `ua`)
- **Placeholder**: Custom placeholder text
- **Required**: Mark field as required

## 🎯 Usage

1. Create a new Elementor Form
2. Add a "Phone with Country" field
3. Configure preferred countries and initial country
4. The field will automatically:
   - Show country selection dropdown
   - Apply input masking based on selected country
   - Validate phone number format
   - Store full international phone number

## 🛠️ Technical Details

- **Minimum WordPress**: 5.0
- **Minimum PHP**: 7.4
- **Dependencies**: Elementor Pro (Forms module)
- **External Libraries**:
  - [intl-tel-input](https://github.com/jackocnr/intl-tel-input) for country selection
  - [IMask](https://github.com/uNmAnNeR/imaskjs) for input masking

## 📊 Data Storage

The plugin stores:

- **Main field**: Full international phone number (e.g., `+380991234567`)
- **Hidden fields**:
  - `{field_id}_full`: Full international number
  - `{field_id}_country`: Country code (e.g., `ua`)

## 🐛 Troubleshooting

### Phone numbers not saving in submissions

- Check WordPress debug logs for errors
- Ensure JavaScript is enabled and not blocked
- Verify Elementor Pro is active and up to date

### Auto-updates not working

- Ensure GitHub repository is accessible
- Check if your repository is public or add GitHub token for private repos
- Verify version tags are properly formatted (`v1.0.1`)

## 🔧 Development

### Building from Source

```bash
git clone https://github.com/your-username/elementor-phone-field.git
cd elementor-phone-field
# No build process needed - pure PHP/JS
```

### Creating a Release

1. Update version in `elementor-phone-field.php`
2. Create a new tag: `git tag v1.0.1`
3. Push tags: `git push origin v1.0.1`
4. Create a GitHub release with the tag

## 📄 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📞 Support

For support, please:

1. Check existing [Issues](https://github.com/your-username/elementor-phone-field/issues)
2. Create a new issue with detailed description
3. Include WordPress version, PHP version, and error messages

## 🔄 Changelog

### 1.0.0

- Initial release
- International phone field with country selection
- Input masking and validation
- Auto-update system via GitHub
- Full phone number storage in submissions

## 🏆 Credits

- Built with ❤️ by Sofard
- Uses [intl-tel-input](https://github.com/jackocnr/intl-tel-input) by Jack O'Connor
- Uses [IMask](https://github.com/uNmAnNeR/imaskjs) by Alexey Knyazev
