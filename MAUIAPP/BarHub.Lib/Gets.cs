

using BarHub.Models;
using BarHub.Models.Enums;
using System.Diagnostics;

namespace BarHub.Lib
{
    public class Gets
    {
        private Methods _methods;
        private string _token;
        public Gets(Methods methods)
        {
            _methods = methods;
        }

        public async Task<List<Restaurant>> GetOwners()
        {
            try
            {
                var restaurants = await _methods.GetAsync<List<Restaurant>>(ApiConstants.GET_RESTAURANTS);

                return restaurants;
            } 
            catch (Exception e)
            {
                return null;
            }

        }

        public async Task<List<Section>> GetSections(int id)
        {
            var sections = await _methods.GetAsync<List<Section>>($"{ApiConstants.GET_SECTIONS}/{id}");

            return sections;
        }

        public async Task<List<User>> GetWorkers(int id)
        {
            var workers = await _methods.GetAsync<List<User>>($"{ApiConstants.GET_WORKERS}/{id}");

            return workers;
        }

        public async Task<List<Table>> GetTables(int id)
        {
            var workers = await _methods.GetAsync<List<Table>>($"{ApiConstants.GET_TABLES}/{id}");

            return workers;
        }

        public async Task<List<Order>> GetOrder(User user, OrderState state)
        {
            try
            {
                var roles = string.Join(",", user.Roles.Select(r => ((int)r).ToString()));
                string url = $"/get_pending_order_lines/{user.Restaurant}/{roles}/{(int)state}";
                return await _methods.GetAsync<List<Order>>(url);
            }
            catch (Exception e)
            {
                
                Trace.WriteLine(e.Message);
                return null;
            }
        }
    }
}
