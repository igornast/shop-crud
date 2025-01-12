import {fetchUtils} from "react-admin";
import {openApiDataProvider} from "@api-platform/admin";
import simpleRestProvider from 'ra-data-simple-rest';
import {getAccessToken} from "./accessToken";

const ApiEntrypoint = process.env.REACT_APP_API_URL || '';
const ApiDocs = `${ApiEntrypoint}/docs`;

const httpClient = async (url: string, options: fetchUtils.Options = {}) => {

    options.headers = new Headers({
        ...options.headers,
        Accept: 'application/json',
    }) as Headers;

    const token = getAccessToken();
    options.user = { token: `Bearer ${token}`, authenticated: !!token };

    return  await fetchUtils.fetchJson(url, options);
}

const jsonDataProvider = openApiDataProvider({
    dataProvider: simpleRestProvider(ApiEntrypoint, httpClient),
    entrypoint: ApiEntrypoint,
    docEntrypoint: ApiDocs,
});

export default jsonDataProvider;