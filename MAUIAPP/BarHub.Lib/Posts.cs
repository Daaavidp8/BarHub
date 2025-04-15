using BarHub.Models;
using System;
using System.Collections.Generic;
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

        public async Task<User> Login(string username, string password)
        {
            var data = new
            {
                username = username,
                password = password
            };

            var user = await _methods.Post<object,User>(ApiConstants.LOGIN, data);

            return user;
        }



    }
}
