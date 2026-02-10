// services/MemberService.js
import axios from 'axios';

const API_URL = '/api/members';

class MemberService {
    async getAll() {
        const response = await axios.get(API_URL);
        return response.data;
    }

    async create(member) {
        const response = await axios.post(API_URL, member);
        return response.data;
    }

    async update(id, member) {
        const response = await axios.put(`${API_URL}/${id}`, member);
        return response.data;
    }

    async delete(id) {
        const response = await axios.delete(`${API_URL}/${id}`);
        return response.data;
    }
}

export default new MemberService();
