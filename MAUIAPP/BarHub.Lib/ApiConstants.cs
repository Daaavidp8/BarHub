using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Lib
{
    public static class ApiConstants
    {
        // Base URL para la Api
#if DEBUG
        //public const string BaseUrl = "http://10.0.2.2:41063";
        public const string BaseUrl = "http://192.168.1.203:41063";
#elif RELEASE
        public const string BaseUrl = "http://192.168.1.203:41063";
#endif


        // EndPoints

        // Autenticación
        public const string LOGIN = "/get_sesion";

        // Admin
        public const string GET_RESTAURANTS = "/get_owners";
        public const string GET_RESTAURANT = "/get_owner";
        public const string CREATE_RESTAURANT = "/create_owner";
        public const string UPDATE_RESTAURANT = "/update_owner";
        public const string DELETE_RESTAURANT = "/delete_owner";

        // Owner
        public const string GET_SECTIONS = "/get_sections";
        public const string GET_SECTION = "/get_section";
        public const string CREATE_SECTION = "/create_section";
        public const string UPDATE_SECTION = "/update_section";
        public const string DELETE_SECTION = "/delete_section";
        public const string GET_ARTICLES = "/get_articles";
        public const string GET_ARTICLE = "/get_article";
        public const string CREATE_ARTICLE = "/create_article";
        public const string UPDATE_ARTICLE = "/update_article";
        public const string DELETE_ARTICLE = "/delete_article";
        public const string GET_WORKERS = "/get_workers";
        public const string GET_WORKER = "/get_worker";
        public const string CREATE_WORKER = "/create_worker";
        public const string UPDATE_WORKER = "/update_worker";
        public const string DELETE_WORKER = "/delete_worker";


        // Waiter
        public const string GET_TABLES = "/get_tables";
        public const string GET_QR = "/get_qrTable";
        public const string ADD_TABLE = "/add_table";
        public const string DELETE_TABLE = "/delete_table";




    }
}
