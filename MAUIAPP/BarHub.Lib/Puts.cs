using BarHub.Models;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Lib
{
    public class Puts
    {
        private Methods _methods;
        public Puts(Methods methods)
        {
            _methods = methods;
        }

        public async Task ModifyRestaurant(Restaurant restaurant)
        {
            try
            {
                var data = new
                {
                    owner_name = restaurant.Name,
                    owner_CIF = restaurant.Cif,
                    owner_contact_email = restaurant.Email,
                    owner_contact_phone = restaurant.Phone,
                    owner_logo = restaurant.Logo,
                };

                var user = await _methods.Put<object, User>($"{ApiConstants.UPDATE_RESTAURANT}/{restaurant.Id}", data);
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }

        }
    }
}
