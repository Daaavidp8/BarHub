using BarHub.Models;
using BarHub.Models.Enums;
using System.Diagnostics;
using Section = BarHub.Models.Section;

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

        public async Task<Article?> CreateArticle(Article article, int? id)
        {
            try
            {
                var data = new
                {
                    article_name = article.Name,
                    article_price = article.Price,
                    article_img = article.Image
                };

                return await _methods.Post<object, Article>($"{ApiConstants.CREATE_ARTICLE}/{id}", data);
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
                return null;
            }
        }

        public async Task<User?> CreateWorker(User user, int? id)
        {
            try
            {
                var data = new
                {
                    worker_name = user.Name,
                    worker_username = user.Username,
                    worker_password = user.Password,
                    worker_roles = user.Roles
                };

                return await _methods.Post<object, User>($"{ApiConstants.CREATE_WORKER}/{id}", data);
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
                return null;
            }
        }

        public async Task<Table> CreateTable(int restaurant)
        {
            try
            {
                return await _methods.Post<Table>($"{ApiConstants.ADD_TABLE}/{restaurant}");
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
                return null;
            }
        }

        
    }
}
