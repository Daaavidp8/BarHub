// API Configuration
export const API_CONFIG = {
    BASE_URL: 'http://localhost:41063', // Updated to match the server's address
    TIMEOUT: 10000,
    HEADERS: {
        'Content-Type': 'application/json'
    },
    withCredentials: false
};

// API Endpoints
export const ENDPOINTS = {
    LOGIN: '/get_sesion',
    GET_OWNER: '/get_owner',
    GET_OWNERS: '/get_owners',
    GET_SECTIONS: '/get_sections',
    GET_ARTICLES: '/get_articles',
    GET_TABLES: '/get_tables', 
    GET_WORKERS: '/get_workers',
    CREATE_OWNER: '/create_owner',
    UPDATE_OWNER: '/update_owner',
    DELETE_OWNER: '/delete_owner',
    GET_NUMBER_ARTICLES: '/get_number_articles',
    CREATE_ROW_BASKET: '/create_row_basket', 
    RESUME_ORDER: '/resumeOrder',
    GET_NUMBER_ARTICLES: '/get_number_articles',
    ORDER_LOG: '/order_log',
    DELETE_BASKET: '/delete_basket',
    UPDATE_SECTION: '/update_section',
    UPDATE_ARTICLE: '/update_article',
    DELETE_ARTICLE: '/delete_article_basket',
    GET_SECTION: '/get_section',
    CREATE_SECTION: '/create_section',
    CREATE_ARTICLE: '/create_article',
    ORDER: "/set_order_to_prep"
};

// Local Storage Keys
export const STORAGE_KEYS = {
    TOKEN: 'token',
    USER_ID: 'id',
    USERNAME: 'username',
    PASSWORD: 'password',
    RESTAURANT: 'restaurant'
};

// Role IDs
export const ROLES = {
    ADMIN: 1,
    OWNER: 2,
    WAITER: 3
};