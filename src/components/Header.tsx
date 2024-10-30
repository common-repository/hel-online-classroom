import React from "react";
import { Button, Space } from "antd";
import {
    SettingOutlined,
    PlusOutlined,
    QuestionCircleOutlined,
} from "@ant-design/icons";

export default function Header({
    handleSttingsModalOpen,
    handleCreateClassModalOpen,
    loading,
}) {
    return (
        <header className="app-header">
            <div
                style={{
                    display: "flex",
                    width: "100%",
                    justifyContent: "space-between",
                    alignItems: "center",
                }}
            >
                <h2 className="app-title">Online Classroom from HigherEdLab.com</h2>
                <Space>
                    <Button
                        icon={<PlusOutlined />}
                        className="settings-btn"
                        disabled={loading}
                        onClick={handleCreateClassModalOpen}
                    >
                        Add New Class
                    </Button>
                    <Button
                        disabled={loading}
                        icon={<SettingOutlined />}
                        className="settings-btn"
                        onClick={handleSttingsModalOpen}
                    >
                        Settings
                    </Button>
                    <Button
                        disabled={loading}
                        icon={<QuestionCircleOutlined />}
                        className="settings-btn"
                        onClick={() =>
                            window.open(
                                "https://higheredlab.com/wordpress-plugin/",
                                "_blank"
                            )
                        }
                    >
                        Help
                    </Button>
                </Space>
            </div>
        </header>
    );
}
