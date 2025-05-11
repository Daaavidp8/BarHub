
using BarHub.Models;
using BarHub.Models.Enums;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Reflection.Metadata;
using System.Text;
using System.Text.Json;
using System.Text.Json.Serialization;
using System.Threading.Tasks;
using JsonSerializer = Newtonsoft.Json.JsonSerializer;

namespace BarHub.Lib
{
    public class Methods : HttpClient
    {
        private string _token;
        public delegate void PreRequestHandler();

        public event PreRequestHandler OnPreRequest;


        public Methods(string baseAddress)
        : this(new Uri(baseAddress))
        {
        }

        public Methods(Uri baseAddress)
            : base()
        {
            this.BaseAddress = baseAddress;
            this.DefaultRequestHeaders.Accept.Clear();
            this.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
        }




        public string GetToken()
        {
            return _token;
        }
        public void SetToken(string token)
        {
            _token = token;
        }

        public async Task<U> Post<T, U>(string addressSuffix, T t) where T : class
        {
            PreRequestCall();

            // Convertir el objeto T en una cadena JSON usando Newtonsoft.Json
            string jsonContent = JsonConvert.SerializeObject(t);
            var httpContent = new StringContent(jsonContent, Encoding.UTF8, "application/json");

            // Establecer Content-Length para evitar Transfer-Encoding: chunked
            httpContent.Headers.ContentLength = Encoding.UTF8.GetByteCount(jsonContent);

            // Realizar la solicitud POST
            HttpResponseMessage response = await this.PostAsync(addressSuffix, httpContent);
            response.EnsureSuccessStatusCode();

            // Leer y deserializar la respuesta usando Newtonsoft.Json
            var responseContent = await response.Content.ReadAsStringAsync();

            // Usar configuraciones adicionales si es necesario
            var settings = new JsonSerializerSettings
            {
                ContractResolver = new Newtonsoft.Json.Serialization.CamelCasePropertyNamesContractResolver(),
                NullValueHandling = NullValueHandling.Ignore,
                MissingMemberHandling = MissingMemberHandling.Ignore
            };

            // Deserializar la respuesta
            U result = JsonConvert.DeserializeObject<U>(responseContent, settings);

            return result;
        }

        public async Task<U> Post<U>(string addressSuffix)
        {
            PreRequestCall();

            HttpResponseMessage response = await this.PostAsync(addressSuffix, null);
            response.EnsureSuccessStatusCode();

            string responseContent = await response.Content.ReadAsStringAsync();

            JsonSerializerSettings settings = new JsonSerializerSettings
            {
                ContractResolver = new Newtonsoft.Json.Serialization.CamelCasePropertyNamesContractResolver(),
                NullValueHandling = NullValueHandling.Ignore,
                MissingMemberHandling = MissingMemberHandling.Ignore
            };

            U result = JsonConvert.DeserializeObject<U>(responseContent, settings);

            return result;
        }



    public async Task<U> Put<T, U>(string addressSuffix, T t) where T : class
    {
        PreRequestCall();

        // Convertir el objeto T en una cadena JSON usando Newtonsoft.Json
        string jsonContent = JsonConvert.SerializeObject(t);
        var httpContent = new StringContent(jsonContent, Encoding.UTF8, "application/json");

        // Establecer Content-Length para evitar Transfer-Encoding: chunked
        httpContent.Headers.ContentLength = Encoding.UTF8.GetByteCount(jsonContent);

        // Realizar la solicitud PUT
        HttpResponseMessage response = await this.PutAsync(addressSuffix, httpContent);
        response.EnsureSuccessStatusCode();

        // Leer y deserializar la respuesta usando Newtonsoft.Json
        var responseContent = await response.Content.ReadAsStringAsync();

        // Usar configuraciones adicionales si es necesario
        var settings = new JsonSerializerSettings
        {
            ContractResolver = new Newtonsoft.Json.Serialization.CamelCasePropertyNamesContractResolver(),
            NullValueHandling = NullValueHandling.Ignore,
            MissingMemberHandling = MissingMemberHandling.Ignore
        };

        // Deserializar la respuesta
        U result = JsonConvert.DeserializeObject<U>(responseContent, settings);

        return result;
    }


        public async Task<U> Delete<U>(string addressSuffix)
        {
            PreRequestCall();

            // Realizar la solicitud DELETE
            HttpResponseMessage response = await this.DeleteAsync(addressSuffix);
            response.EnsureSuccessStatusCode();

            // Leer la respuesta
            var responseContent = await response.Content.ReadAsStringAsync();

            // Usar configuraciones adicionales si es necesario
            var settings = new JsonSerializerSettings
            {
                ContractResolver = new Newtonsoft.Json.Serialization.CamelCasePropertyNamesContractResolver(),
                NullValueHandling = NullValueHandling.Ignore,
                MissingMemberHandling = MissingMemberHandling.Ignore
            };

            // Deserializar la respuesta
            U result = JsonConvert.DeserializeObject<U>(responseContent, settings);

            return result;
        }



        public async Task<T> GetAsync<T>(string AddressSuffix)
        {
                PreRequestCall();
                HttpResponseMessage response = await this.GetAsync(AddressSuffix);
                response.EnsureSuccessStatusCode();

            var responseContent = await response.Content.ReadAsStringAsync();

            T t = await response.Content.ReadAsAsync<T>();
                return t;
        }


        protected void PreRequestCall()
        {
            if (!string.IsNullOrEmpty(_token))
            {
                if (this.DefaultRequestHeaders.Contains("Authorization"))
                {
                    this.DefaultRequestHeaders.Remove("Authorization");
                }

                this.DefaultRequestHeaders.Add("Authorization", $"Bearer {_token}");
            }
        }

    }
        }
