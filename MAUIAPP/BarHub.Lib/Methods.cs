
using BarHub.Models;
using BarHub.Models.Enums;
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

        public async Task<U> Post<T, U>(string addressSuffix, T t)
where T : class
        {
                PreRequestCall();

                // Convertir el objeto T en una cadena JSON usando System.Text.Json
                string jsonContent = JsonSerializer.Serialize(t);
                var httpContent = new StringContent(jsonContent, Encoding.UTF8, "application/json");

                // Establecer Content-Length para evitar Transfer-Encoding: chunked
                httpContent.Headers.ContentLength = Encoding.UTF8.GetByteCount(jsonContent);

                // Realizar la solicitud POST
                HttpResponseMessage response = await this.PostAsync(addressSuffix, httpContent);
                response.EnsureSuccessStatusCode();

                // Leer y deserializar la respuesta usando System.Text.Json
                var responseContent = await response.Content.ReadAsStringAsync();

                // Usar opciones para configuraciones adicionales
                var options = new JsonSerializerOptions
                {
                    PropertyNameCaseInsensitive = true, // Manejo insensible a mayúsculas/minúsculas
                    IncludeFields = true, // Incluye campos en la deserialización
                    DefaultIgnoreCondition = JsonIgnoreCondition.WhenWritingNull,
                    //Converters = { new EnumListConverter<Roles>() }
                };

                U result = JsonSerializer.Deserialize<U>(responseContent, options);

                return result;
           
        }

        public async Task<U> Put<T, U>(string addressSuffix, T t)
    where T : class
        {
            PreRequestCall();

            // Convertir el objeto T en una cadena JSON
            string jsonContent = JsonSerializer.Serialize(t);
            var httpContent = new StringContent(jsonContent, Encoding.UTF8, "application/json");

            // Establecer Content-Length
            httpContent.Headers.ContentLength = Encoding.UTF8.GetByteCount(jsonContent);

            // Realizar la solicitud PUT
            HttpResponseMessage response = await this.PutAsync(addressSuffix, httpContent);
            response.EnsureSuccessStatusCode();

            // Leer y deserializar la respuesta
            var responseContent = await response.Content.ReadAsStringAsync();

            var options = new JsonSerializerOptions
            {
                PropertyNameCaseInsensitive = true,
                IncludeFields = true,
                DefaultIgnoreCondition = JsonIgnoreCondition.WhenWritingNull
            };

            U result = JsonSerializer.Deserialize<U>(responseContent, options);

            return result;
        }

        public async Task<U> Delete<U>(string addressSuffix)
        {
            PreRequestCall();

            // Realizar la solicitud DELETE
            HttpResponseMessage response = await this.DeleteAsync(addressSuffix);
            response.EnsureSuccessStatusCode();

            // Leer y deserializar la respuesta
            var responseContent = await response.Content.ReadAsStringAsync();

            var options = new JsonSerializerOptions
            {
                PropertyNameCaseInsensitive = true,
                IncludeFields = true,
                DefaultIgnoreCondition = JsonIgnoreCondition.WhenWritingNull
            };

            U result = JsonSerializer.Deserialize<U>(responseContent, options);

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
