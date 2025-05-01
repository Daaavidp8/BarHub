

using BarHub.Models;

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
            var restaurants = await _methods.GetAsync<List<Section>>($"{ApiConstants.GET_SECTIONS}/{id}");

            return restaurants;
        }
    }
}
