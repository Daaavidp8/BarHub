using BarHub.Models;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;

namespace BarHub.Lib
{

    public class Posts
    {
        private Methods _methods;
        public Posts(Methods methods)
        {
            _methods = methods;
        }

        public async Task<User?> Login(string username, string password)
        {
            try
            {
                var data = new
                {
                    username = username,
                    password = password
                };

                var user = await _methods.Post<object, User>(ApiConstants.LOGIN, data);
                _methods.SetToken(user.Token);
                return user;
            }
            catch (Exception e)
            {
                return null;
            }

        }

        public async Task<Restaurant?> CreateRestaurant(Restaurant restaurant)
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

                return await _methods.Post<object, Restaurant>(ApiConstants.CREATE_RESTAURANT, data);
                
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
                return null;
            }

        }

        public async Task<Section?> CreateSection(Section section,int? id)
        {
            try
            {
                var data = new
                {
                    section_name = section.Name,
                    section_img = section.Image
                };

                return await _methods.Post<object, Section>($"{ApiConstants.CREATE_SECTION}/{id}", data);
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
                return null;
            }

        }


    }
}
