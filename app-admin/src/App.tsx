import {OpenApiAdmin} from '@api-platform/admin';
import authProvider from "./components/authProvider";
import jsonDataProvider from "./components/jsonDataProvider";

const ApiEntrypoint = process.env.REACT_APP_API_URL || '';
const ApiDocs = `${ApiEntrypoint}/docs`;

export default () => <OpenApiAdmin
    entrypoint={ApiEntrypoint}
    docEntrypoint={ApiDocs}
    dataProvider={jsonDataProvider}
    authProvider={authProvider}
/>