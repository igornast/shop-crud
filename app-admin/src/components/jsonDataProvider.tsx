import {fetchUtils} from "react-admin";
import {openApiDataProvider} from "@api-platform/admin";
import simpleRestProvider from 'ra-data-simple-rest';
import {getAccessToken} from "./accessToken";

const httpClient = async (url: string, options: fetchUtils.Options = {}) => {

    options.headers = new Headers({
        Accept: 'application/json',
    }) as Headers;

    const token = getAccessToken();
    options.user = { token: `Bearer ${token}`, authenticated: !!token };

    const { status, headers, body, json } = await fetchUtils.fetchJson(url, options);
    console.log('fetchJson result', { status, headers, body, json });
    return { status, headers, body, json };
}

const jsonDataProvider = openApiDataProvider({
    dataProvider: simpleRestProvider("http://localhost/api", httpClient),
    entrypoint: "http://localhost/api",
    docEntrypoint: "http://localhost/api/docs",
});


export default jsonDataProvider;