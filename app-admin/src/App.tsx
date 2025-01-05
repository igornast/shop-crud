import {OpenApiAdmin} from '@api-platform/admin';
import authProvider from "./components/authProvider";
import jsonDataProvider from "./components/jsonDataProvider";

export default () => <OpenApiAdmin
    entrypoint="http://localhost/api"
    docEntrypoint="http://localhost/api/docs"
    dataProvider={jsonDataProvider}
    authProvider={authProvider}
/>

