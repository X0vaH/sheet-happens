---
name: react-janitor
description: >
    Use this skill whenever refactoring, reviewing, or writing React/JSX/TSX components — especially when the user asks to make components "atomic", "reusable", "cleaner", "DRY", or to "split up" a component, or when new component code or a stylesheet is about to be written. Enforces seven rules: components stay atomic/single-purpose; near-duplicate components merge into one reusable configurable component; components never import other components (only pages/containers compose them); components never define their own methods/handlers (logic comes in as callback props); backend/data interaction happens only through hooks; code stays DRY overall (shared logic/hooks/utils/types, not copy-paste); and CSS files live only in src/styles, all imported into one main stylesheet imported just once in App.jsx. Trigger proactively whenever creating/editing a .jsx/.tsx or .css file, not only when named explicitly.
---

# React Janitor

A skill for refactoring and authoring React components under seven strict architectural rules. These rules trade a bit of extra plumbing (prop-drilling, hook files, shared utils, a centralized styles folder) for components that are trivially testable, reusable, and swappable — and a codebase with no copy-pasted logic or scattered stylesheets.

Apply this skill whenever writing new component code or refactoring existing component code — don't wait for the user to ask for "the react-refactor skill" by name.

## The Seven Rules

### 1. Atomicity — one component, one job

- Each component should render one coherent piece of UI and do one thing.
- If a component's JSX has more than ~2 clearly separable visual regions (e.g. a header block + a list + a footer block), split each region into its own component.
- A component that needs a comment like `{/* --- section 2 --- */}` to organize its own JSX is a signal it should be split.
- Prefer many small files (`Avatar.tsx`, `UserName.tsx`, `UserCard.tsx`) over one large file with nested inline sub-JSX.
- Atomic does not mean "trivial" — a component can be complex in props/behavior as long as it renders one concern.

### 2. Merge near-duplicates into one reusable component

- Before creating a new component, actively check the codebase (grep by rendered output / prop shape, not just name) for an existing component that renders visually or structurally similar markup.
- If two components differ only in: labels, icons, color/variant, size, or which callback fires — merge them into one component parameterized by props (e.g. `variant`, `size`, `icon`, `label`), rather than keeping `PrimaryButton` and `SecondaryButton` as separate files.
- Do NOT merge components that differ in structure/semantics just to reduce file count — only merge when the JSX shape and purpose are actually the same. Forcing a merge with a pile of conditional branches (`if (type === 'a') return <X/> else return <Y/>`) is worse than two components; that's a sign they're not actually the same component.
- When merging, keep the new component's prop surface small and explicit — avoid a generic `props: any` grab-bag.

### 3. Components never import other components — only pages/containers compose

- A file under `components/` (or your project's component folder) must not `import` another component from `components/`.
- Composition happens one level up: a **page**, **screen**, **route**, or **container** file is the only place allowed to import multiple components and arrange them together.
- This means components receive their content via `children` or explicit render-prop/slot props, rather than reaching out and importing a sibling component to embed.
- Concretely:
    - ✅ `pages/Dashboard.tsx` imports `Header`, `Sidebar`, `WidgetGrid` and lays them out.
    - ❌ `components/WidgetGrid.tsx` imports `components/Widget.tsx` directly.
    - If `WidgetGrid` needs to render a list of `Widget`s, the _page_ passes an already-built array of `<Widget />` elements (or a `renderItem` callback) down as a prop/children, rather than `WidgetGrid` importing `Widget` itself.
- Exception: purely generic, structural "leaf" primitives that ship with a UI kit (e.g. a `<Box>` or `<Text>` wrapper with no app-specific meaning) may be treated as markup, not "a component" for this rule — use judgment, and prefer flagging this exception to the user rather than silently assuming it.

### 4. No methods defined inside a component — logic comes in as callback props

- A component's function body should not define its own event handlers, computed-value functions, or business logic inline (no `const handleClick = () => {...}` written inside the component).
- Instead, the component accepts handlers as props (`onClick`, `onSubmit`, `onValueChange`, etc.) and simply calls them.
- Purely trivial, stateless UI-only wiring (e.g. `onClick={() => setOpen(!open)}` for a dropdown's own open/closed local UI state) is the narrow exception — state that is 100% private presentation state and never touches data/business logic can stay local. Anything that touches data, validation, side effects, or app logic must be a passed-in callback, not a locally defined method.
- This means components are close to pure render functions of `props` (+ minimal private UI state), which makes them trivially testable and reusable across different pages with different behavior wired in.

### 5. Backend/data interactions happen only through hooks

- A component must never call `fetch`, an API client, GraphQL client, or any backend/service call directly in its body or in a locally defined handler.
- All data-fetching, mutations, subscriptions, and caching go through hooks — either React's built-ins (`useEffect` + state) wrapped in a custom hook, or a data-layer hook (e.g. `useQuery`/`useMutation` from React Query, SWR, Apollo, RTK Query).
- Convention: put backend-interaction hooks in a `hooks/` folder, one hook per resource/concern (`useUser.ts`, `useCreatePost.ts`), returning `{ data, isLoading, error, ...actions }`.
- The component calls the hook, destructures what it needs, and renders — it never knows _how_ the data got there.
- Combined with rule 4: a component that needs to "save on click" receives `onSave` as a prop from its page; the _page_ is what calls `useSaveThing()` and passes the resulting mutate function down. This keeps the component fully decoupled from any backend concern and easy to reuse/test/storybook.

### 6. Keep code DRY beyond just components

- Rule 2 covers duplicate _components_; this rule covers everything else — duplicate logic, duplicate JSX fragments, duplicate types/constants, duplicate hooks.
- Before writing new logic, search for an existing utility/hook/type that already does it. Repeated: validation logic, formatting logic (dates, currency, text truncation), constants (route strings, enum-like unions), and small JSX fragments (icons + label pairs, empty-states, loading spinners) should be extracted rather than re-typed.
- Extraction targets, by kind of duplication:
    - Repeated **backend/data logic** → a shared hook under `hooks/` (ties in with rule 5).
    - Repeated **pure computation** (formatting, sorting, validation) → a plain function under `utils/` or `lib/`, imported by hooks or pages — not copy-pasted into multiple components.
    - Repeated **small JSX fragments** that aren't a full "component" per se (e.g. a spinner, an icon+label pair) → still becomes its own atomic component (rule 1) rather than being duplicated inline.
    - Repeated **types/constants** → a shared `types/` or `constants/` module.
- Don't over-abstract: two pieces of code that look similar today but represent unrelated concerns (coincidental duplication) should stay separate. Only extract when the duplication represents the _same_ concern reused in multiple places — extracting coincidentally-similar code creates a false shared dependency that becomes awkward the moment the two use cases diverge.
- When refactoring, actively grep for repeated patterns (not just what the user pointed at) and flag/extract them as part of the same pass, rather than only fixing the one spot the user mentioned.

### 7. Stylesheets live in one central folder, imported through a single main CSS file

- CSS files (`.css`, and equivalents like `.scss`/`.less` if the project uses them) must NOT sit alongside their component (no co-located `Button.css` next to `Button.tsx`).
- All stylesheets go in a dedicated folder, e.g. `src/styles/`, one file per component/concern (`src/styles/Button.css`, `src/styles/UserCard.css`), mirroring the component's name so the mapping stays obvious.
- Every file in `src/styles/` gets `@import`-ed into one single aggregator file — the "main" stylesheet (e.g. `src/styles/main.css` or `src/index.css`). This main file is the ONLY stylesheet that is ever imported from JS/TSX — no component or page should `import "./SomeComponent.css"` directly.
- The main stylesheet is imported exactly once, in `App.jsx`/`App.tsx` (the app root) — never re-imported in individual pages or components. Because CSS is global once loaded, this single import at the root gives every page and component access to all style rules without each of them needing their own import.
- Consequence: components/pages apply styles via `className="button button--primary"` etc. — they rely on the class existing globally, they don't import the CSS that defines it.
- When adding a new component's styles: create `src/styles/ComponentName.css`, add `@import "./ComponentName.css";` to the main stylesheet, and never add a second import path for it anywhere else.
- If the project uses CSS Modules or CSS-in-JS (styled-components, Emotion, etc.), flag the conflict to the user rather than silently forcing this pattern — this rule assumes plain global CSS classes.

## Refactoring Workflow

When asked to refactor existing component(s):

1. **Read the target file(s) fully** before changing anything.
2. **Diagnose against all 7 rules** — list, in a short scratch-pad, which rules the current code violates (e.g. "imports 2 sibling components", "defines `handleSubmit` inline", "calls `fetch` directly in `useEffect`", "duplicates the date-formatting logic already in `utils/format.ts`", "has a co-located `Button.css`").
3. **Check for near-duplicates** (rules 2 & 6) — search the codebase for similarly-shaped existing components, hooks, and utility functions before creating new ones.
4. **Split first, then relocate imports.** Break oversized components into atomic pieces (rule 1) before worrying about who imports whom — it's easier to fix import direction once the pieces already exist.
5. **Extract inline logic into hooks/callbacks**:
    - Backend calls → move into a new or existing hook under `hooks/`.
    - Local handlers that touch data/business logic → become props the parent (page/container) supplies.
6. **Rewire composition**: move multi-component imports up into the nearest page/container; components below only receive props/children.
7. **Relocate and consolidate CSS**: move any co-located `.css` file into `src/styles/`, add/confirm its `@import` in the main stylesheet, and remove any direct per-component CSS import — the component should only rely on the global class being available via the single `App.jsx` import.
8. **Show a before/after summary**: which files were split/created/merged/moved, and which rule each change addresses. Don't silently rewrite a whole tree without explaining the mapping from old file(s) to new file(s).
9. **Preserve behavior.** Refactors under this skill are structural — visual output and app behavior should be unchanged unless the user asked for behavior changes too.

## Quick Reference: Good vs. Bad

```
// ❌ Bad — CSS co-located with components, each importing its own file
src/
  components/
    UserCard.tsx        (import "./UserCard.css")
    UserCard.css
    Button.tsx           (import "./Button.css")
    Button.css

// ✅ Good — all styles centralized, one aggregator, one root import
src/
  App.jsx                (import "./styles/main.css"  ← only place this happens)
  components/
    UserCard.tsx          (no CSS import — just className="user-card")
    Button.tsx            (no CSS import — just className="button button--primary")
  styles/
    main.css              (@import "./UserCard.css"; @import "./Button.css"; ...)
    UserCard.css
    Button.css
```

```tsx
// ❌ Violates rules 3, 4, 5 — imports a sibling component, defines a handler,
// and fetches data directly inside the component.
import { UserAvatar } from './UserAvatar';

function UserCard({ userId }) {
    const [user, setUser] = useState(null);

    useEffect(() => {
        fetch(`/api/users/${userId}`)
            .then((r) => r.json())
            .then(setUser);
    }, [userId]);

    const handleFollow = () => {
        fetch(`/api/users/${userId}/follow`, { method: 'POST' });
    };

    return (
        <div>
            <UserAvatar user={user} />
            <button onClick={handleFollow}>Follow</button>
        </div>
    );
}
```

```tsx
// ✅ hooks/useUser.ts — all backend interaction lives in a hook
export function useUser(userId: string) {
    const [user, setUser] = useState<User | null>(null);
    useEffect(() => {
        fetch(`/api/users/${userId}`)
            .then((r) => r.json())
            .then(setUser);
    }, [userId]);
    return { user };
}

// ✅ hooks/useFollowUser.ts
export function useFollowUser(userId: string) {
    return useCallback(() => {
        fetch(`/api/users/${userId}/follow`, { method: 'POST' });
    }, [userId]);
}

// ✅ components/UserCard.tsx — pure render, no imports of other components,
// no inline methods, no backend calls. Receives everything via props.
function UserCard({ user, onFollow }) {
    return (
        <div>
            <span>{user?.name}</span>
            <button onClick={onFollow}>Follow</button>
        </div>
    );
}

// ✅ pages/UserPage.tsx — the only place that composes components + wires hooks
function UserPage({ userId }) {
    const { user } = useUser(userId);
    const followUser = useFollowUser(userId);

    return (
        <div>
            <UserAvatar user={user} />
            <UserCard user={user} onFollow={followUser} />
        </div>
    );
}
```

## When Rules Are in Tension

- If following a rule strictly would require awkward, deep prop-drilling (many layers passing the same prop through unchanged), suggest introducing a page-level composition helper or lifting state up rather than breaking rule 3/4 — but flag the trade-off to the user rather than silently prop-drilling 5+ levels.
- If the project already has an established pattern that conflicts with one of these rules (e.g. a context-provider pattern for backend data instead of hooks), ask the user whether to follow the existing convention or apply this skill's rules, rather than assuming.
