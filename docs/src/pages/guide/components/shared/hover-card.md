---
sidebarPos: 12
sidebarTitle: Hover Card
---
# Hover Card

`ue-hover-card` shows a compact activator (avatar and title) that opens a preview card on hover or click. The card can show a title, subtitle, avatar, detail fields, and action links. Everything is driven by dot-path configuration, so the same component works for users, companies, or any other record.

The card renders the **object** you give it. In data tables, that object comes from the row, which the module has already eager-loaded, so the hover card makes no extra request.

## Usage

```html
<ue-hover-card
  :item="user"
  :title="['{name} {surname}', 'email']"
  subtitle="email"
  avatar="avatar"
  :fields="[{ key: 'company_name', label: 'Company', icon: 'mdi-domain' }]"
  :actions="[{ label: 'Go to user', href: '/system/system-users/:id/edit', appendIcon: 'mdi-arrow-right' }]"
/>
```

## In data tables (PHP formatter)

Use the `hoverCard` formatter on a column. Its options become the component props, and the cell value becomes `item`. What the card shows is configured here, in the header.

The cell value must be an **object** that is already on the row: a relation, or an accessor built from an eager-loaded relation. Load it in the module's index `with`. The table formatter never fetches. `endpoint`, `params`, and `itemKey` in formatter options are ignored, and a scalar value renders `emptyText`.

Example from SystemNotification. The relation is eager-loaded once per page:

```php
// Blueprint/Notification/Index/NotificationIndexWith.php
return ['recipient'];
```

The `Notification::recipient_summary` accessor turns it into a compact object (`id`, `full_name`, `email`, `company_name`, `avatar`). The column then points at that object:

```php
[
    'title' => 'Recipient',
    'key' => 'recipient_summary',
    'sortable' => false,
    'formatter' => [
        'hoverCard',
        [
            'title' => ['full_name', 'email'],
            'subtitle' => 'email',
            'avatar' => 'avatar',
            'fields' => [
                ['key' => 'company_name', 'label' => __('Company'), 'icon' => 'mdi-domain'],
            ],
            'actions' => [
                ['label' => __('Go to user'), 'href' => $editUrl, 'appendIcon' => 'mdi-arrow-right'],
            ],
        ],
    ],
],
```

Here `$editUrl` keeps an `:id` placeholder, for example `Modularous::find('SystemUser')->getRouteActionUrl('User', 'edit', ['user' => ':id'])`. The placeholder is filled per row from the object.

::: tip Summary objects
Avoid `name` / `title` keys in the cell object. The index formatter collapses objects that have them into a scalar. Use keys such as `full_name`.
:::

## Fetching by id (standalone, optional)

Outside tables, for example in a custom component without preloaded data, pass an id as `item` and set `endpoint` to the target module's index URL. Every card that mounts in the same tick shares **one** `GET {endpoint}?ids=1,2,3` request, and results are cached for the page. If `item` is an object, `endpoint` is ignored.

```html
<ue-hover-card
  :item="ownerId"
  endpoint="/system/system-users"
  :params="{ eagers: 'company,fileponds' }"
  :title="['{name} {surname}', 'email']"
  avatar="avatar"
/>
```

The endpoint is the `ids` branch of every module controller's `index` action (`ManageIndexAjax::respondToIndexAjaxByIds`). `params` are forwarded as query parameters:

| Param | Effect |
|-------|--------|
| `eagers` | Relations to eager-load (comma-separated), e.g. `company,fileponds` |
| `appends` | Extra model accessors to append |
| `scopes` | Repository scopes to apply |

While loading, the activator shows a skeleton. If the record is missing or the request fails (for example, a `403` because the viewer cannot list that module), `emptyText` is shown. Failed requests are not cached.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `item` | `Object\|String\|Number` | `null` | Record object; an id only together with `endpoint` |
| `endpoint` | `String` | `null` | Standalone only: module index URL used to fetch a scalar `item` |
| `params` | `Object` | `{}` | Extra query for the endpoint (`eagers`, `appends`, `scopes`) |
| `itemKey` | `String` | `'id'` | Field matching fetched records to the requested id |
| `title` | `String\|Array` | `['title', 'name']` | Heading path(s); an array falls back to the next non-empty value |
| `subtitle` | `String\|Array` | `null` | Subtitle path(s) |
| `avatar` | `String\|Array` | `null` | Image URL path(s); omit to hide the avatar |
| `avatarIcon` | `String` | `'mdi-account'` | Icon shown when the avatar value is empty |
| `fields` | `Array` | `[]` | Detail rows (see below) |
| `actions` | `Array` | `[]` | Footer buttons (see below) |
| `emptyText` | `String` | `'-'` | Shown when there is no record |
| `openOnHover` | `Boolean` | `true` | `false` opens on click (tables pass `false` on mobile) |
| `openDelay` | `Number\|String` | `250` | Hover delay in ms |
| `location` | `String` | `'bottom start'` | `v-menu` location |
| `minWidth` / `maxWidth` | `Number\|String` | `260` / `340` | Card width bounds |
| `disabled` | `Boolean` | `false` | Disable the menu |

### Paths and templates

`title`, `subtitle`, `avatar`, and field `key` accept dot paths (`company.name`). A path containing `{field}` tokens is a template: `'{name} {surname}'` renders `Jane Doe`, collapses blanks, and falls back to the next path when the result is empty.

### `fields`

Each field is either a path string or an object:

| Key | Description |
|-----|-------------|
| `key` | Value path (required) |
| `label` | Row caption |
| `icon` | Prepend icon |
| `format` | `text` (default), `email` (mailto), `phone` (tel), `link` (new tab), `date`, `chip` |
| `dateFormat` | vue-i18n datetime format for `date` (default `medium`) |
| `hideEmpty` | Hide the row when the value is blank (default `true`) |

### `actions`

`{ label, href, icon, appendIcon, target, color, variant }`. `href` supports `:field` placeholders (dot paths allowed), URL-encoded from the record. An action whose placeholder has no value is hidden.

## Slots

| Slot | Props | Purpose |
|------|-------|---------|
| `activator` | `props`, `item`, `title`, `avatar` | Replace the trigger (bind `props` to your element) |
| `header` | `item`, `title`, `subtitle`, `avatar` | Replace the whole card header |
| `prepend` | `item`, `avatar` | Replace the header avatar |
| `title` / `subtitle` | `item`, `value` | Replace heading text |
| `fields` | `item`, `fields` | Replace the field list |
| `field.<key>` | `item`, `field`, `value` | Replace one field value, e.g. `field.email` |
| default | `item` | Extra content below the fields |
| `actions` | `item`, `actions` | Replace the footer |

Slot `item` is always the resolved record object.

```html
<ue-hover-card :item="row.owner" :fields="['email']">
  <template #field.email="{ value }">
    <ue-copy-text :text="value" /> {{ value }}
  </template>
</ue-hover-card>
```

## Programmatic loading

The batching loader is available for other components:

```js
import { loadHoverCardItem, clearHoverCardCache } from '@/utils/hoverCard'

const user = await loadHoverCardItem('/system/system-users', 10, { params: { eagers: 'company' } })
clearHoverCardCache('/system/system-users') // e.g. after the user was edited
```
