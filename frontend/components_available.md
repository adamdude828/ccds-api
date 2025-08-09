# Component Documentation

These are components availabe in @challenger/components npm package

## Atoms

### Button
A flexible button component that supports different variants and sizes.

Props:
- `variant?: 'primary' | 'secondary'` - Button style variant
- `size?: 'sm' | 'md' | 'lg'` - Button size
- `disabled?: boolean` - Disabled state
- `label: string` - Button text
- `onClick?: () => void` - Click handler
- `className?: string` - Additional CSS classes

### Input
A styled text input component.

Props:
- `value: string` - Input value
- `onChange: (value: string) => void` - Change handler
- `placeholder?: string` - Input placeholder text
- `disabled?: boolean` - Disabled state
- `error?: string` - Error message
- `className?: string` - Additional CSS classes

### Checkbox
A custom styled checkbox component.

Props:
- `checked: boolean` - Checkbox state
- `onChange: (checked: boolean) => void` - Change handler
- `label?: string` - Checkbox label
- `disabled?: boolean` - Disabled state
- `className?: string` - Additional CSS classes

### Dropdown
A customizable dropdown select component built with Headless UI.

Props:
- `options: Array<{value: string, label: string}>` - Dropdown options
- `value: string` - Selected value
- `onChange: (value: string) => void` - Change handler
- `placeholder?: string` - Placeholder text
- `disabled?: boolean` - Disabled state
- `className?: string` - Additional CSS classes

### NavItem
A navigation item component used within NavMenu.

Props:
- `label: string` - Navigation item text
- `href: string` - Link destination
- `active?: boolean` - Active state
- `icon?: React.ReactNode` - Optional icon
- `className?: string` - Additional CSS classes

### ColorSwatch
A component to display a color sample.

Props:
- `color: string` - Color value (hex/rgb)
- `size?: 'sm' | 'md' | 'lg'` - Swatch size
- `className?: string` - Additional CSS classes

### ColorItem
A component that displays a color with its label and value.

Props:
- `color: string` - Color value
- `label: string` - Color name/label
- `showValue?: boolean` - Show color value
- `className?: string` - Additional CSS classes

### ColorPalette
Displays a collection of related colors.

Props:
- `colors: Array<{color: string, label: string}>` - Color definitions
- `title?: string` - Palette title
- `className?: string` - Additional CSS classes

## Molecules

### NavMenu
A navigation menu component that contains NavItems.

Props:
- `items: Array<{label: string, href: string, icon?: React.ReactNode}>` - Navigation items
- `orientation?: 'horizontal' | 'vertical'` - Menu orientation
- `className?: string` - Additional CSS classes

### ItemRow
A component for displaying item data in a row format.

Props:
- `item: BaseItem` - Item data to display
- `columns: Column[]` - Column definitions
- `onClick?: () => void` - Click handler
- `selected?: boolean` - Selected state
- `className?: string` - Additional CSS classes

## Organisms

### ItemGrid
A flexible grid system for displaying collections of items.

Props:
- `items: BaseItem[]` - Array of items to display
- `columns: Column[]` - Column definitions
- `layout?: 'grid' | 'list'` - Display layout
- `loading?: boolean` - Loading state
- `onItemClick?: (item: BaseItem) => void` - Item click handler
- `className?: string` - Additional CSS classes

### PageTemplate
A base page layout component with header, navigation, and content areas.
Note: This component is intended for authenticated/logged-in experiences only.

Props:
- `navigation?: React.ReactNode` - Navigation content
- `children: React.ReactNode` - Page content
- `className?: string` - Additional CSS classes
