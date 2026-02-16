# Electronic Integration Management System (EMIS)

React-based device management system for registering, tracking, and reporting issues on organizational electronic assets.

## Stack

- React 19
- React Router
- Bootstrap + custom CSS
- Context API with service layer abstraction

## Implemented Architecture Improvements

- `src/services/` mock API layer backed by local storage
- `src/hooks/` custom hooks for context access and report submission workflow
- `src/components/common/ProtectedRoute.jsx` for route-level auth checks
- `src/components/common/ToastContainer.jsx` for app-wide toast feedback
- Contexts refactored with loading/error-aware async flows
- All source files migrated from `.js` to `.jsx`

## Run Locally

1. Install dependencies:
   `npm install`
2. Start development server:
   `npm start`
3. Open:
   `http://localhost:3000`

## Build

- Production build:
  `npm run build`

## Demo Auth Behavior

- Sign in screen supports:
  - `Admin (HOD)`
  - `Normal User`
- Role is used by protected routes to gate admin-only screens.

## Notes

- Data persistence is currently local-storage based (mock backend behavior).
- This codebase is structured to support a future REST API integration by replacing service implementations in `src/services/`.
