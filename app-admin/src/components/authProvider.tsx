import {jwtDecode} from "jwt-decode";
import {getAccessToken} from "./accessToken";

interface JwtPayload {
    exp?: number;
    iat?: number;
    roles: string[];
    username: string;
}

const ApiAuth = process.env.REACT_APP_API_AUTH || '';

const authProvider = {
    login: ({username, password}: { username: string; password: string }) => {

        const request = new Request(ApiAuth, {
            method: "POST",
            body: JSON.stringify({email: username, password}),
            headers: new Headers({"Content-Type": "application/json"}),
        });

        return fetch(request)
            .then((response) => {
                if (response.status < 200 || response.status >= 300) {
                    throw new Error(response.statusText);
                }
                return response.json();
            })
            .then((auth) => {
                localStorage.setItem("token", auth.token);
            })
            .catch(() => {
                throw new Error("Network error");
            });
    },
    logout: () => {
        localStorage.removeItem("token");
        return Promise.resolve();
    },
    checkAuth: () =>
        getAccessToken() ? Promise.resolve() : Promise.reject(),
    checkError: (error: { status: number }) => {
        const status = error.status;
        if (status === 401 || status === 403) {
            localStorage.removeItem("token");
            return Promise.reject();
        }
        // other error code (404, 500, etc): no need to log out
        return Promise.resolve();
    },
    getIdentity: () => {
        const token = getAccessToken();

        if (!token) return Promise.reject();

        const decoded = jwtDecode<JwtPayload>(token);

        return Promise.resolve({
            id: "",
            fullName: decoded.username,
            avatar: "",
        });
    },
    getPermissions: () => Promise.resolve(""),
};


export default authProvider;