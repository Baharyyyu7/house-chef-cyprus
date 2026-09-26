# Header utility controls

- Root cause: controls used `min-width`, so their content determined different final widths; later header rules also overrode padding.
- Fix: desktop locale, theme, account/admin, login and register controls now share a fixed 9.5rem width and 2.75rem height.
- Regression: `ThemeAndPremiumNavigationTest::test_desktop_utility_controls_use_one_fixed_width`.
- Verification: production build passed; 13 focused tests with 52 assertions passed; live controls measured 152x44 pixels each.
- Production release: `cefaad3`.
